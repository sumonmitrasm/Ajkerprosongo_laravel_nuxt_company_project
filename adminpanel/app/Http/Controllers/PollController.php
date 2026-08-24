<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollComment;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PollController extends Controller
{
    /**
     * Show poll list, search, filters and summary cards.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->search);
        $status = $request->status;
        $type = $request->poll_type;

        // Load only counts instead of loading every vote and comment record.
        $polls = Poll::withCount(['options', 'votes', 'comments'])
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($type, fn ($query) => $query->where('poll_type', $type))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'total_polls' => Poll::count(),
            'live_polls' => Poll::where('status', 'published')
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()))
                ->count(),
            'total_votes' => PollVote::count(),
            'comments' => PollComment::count(),
        ];

        return view('admin.poll.poll', compact(
            'polls', 'summary', 'search', 'status', 'type'
        ));
    }

    /**
     * Validate and save a new poll with its options.
     */
    public function store(Request $request)
    {
        $data = $this->validatePoll($request);
        $image = $request->hasFile('image')
            ? $this->uploadImage($request->file('image'))
            : null;

        try {
            $poll = DB::transaction(function () use ($data, $image) {
                // First create the main poll record.
                $poll = Poll::create($this->pollData($data, $image));

                // Then create all answer options in display order.
                foreach ($data['options'] as $position => $option) {
                    $poll->options()->create([
                        'content' => trim($option['content']),
                        'position' => $position,
                    ]);
                }

                // Convert the selected option position into its database ID.
                $this->saveCorrectAnswer(
                    $poll,
                    $data['correct_option_index'] ?? null
                );

                return $poll;
            });
        } catch (\Throwable $exception) {
            $this->deleteImage($image);
            throw $exception;
        }

        return response()->json([
            'message' => 'Poll created successfully.',
            'poll' => $poll->load('options'),
        ], 201);
    }

    /**
     * Return one poll for the edit modal.
     */
    public function show(Poll $poll)
    {
        $poll->load([
            'options' => fn ($query) => $query->withCount('votes'),
        ]);

        return response()->json([
            'poll' => [
                ...$poll->toArray(),
                'image_url' => $poll->image
                    ? asset('admin/pollimage/'.$poll->image)
                    : null,
                'starts_at_input' => $poll->starts_at?->format('Y-m-d\TH:i'),
                'ends_at_input' => $poll->ends_at?->format('Y-m-d\TH:i'),
            ],
        ]);
    }

    /**
     * Validate and update a poll with its options.
     */
    public function update(Request $request, Poll $poll)
    {
        $data = $this->validatePoll($request, $poll);
        $oldImage = $poll->image;
        $newImage = $request->hasFile('image')
            ? $this->uploadImage($request->file('image'))
            : null;

        try {
            DB::transaction(function () use ($poll, $data, $newImage) {
                // Update the main poll fields.
                $poll->update($this->pollData(
                    $data,
                    $newImage ?: $poll->image,
                    $poll
                ));

                // Update existing options and add newly submitted options.
                $existingIds = $poll->options()->pluck('id');
                $submittedIds = collect($data['options'])
                    ->pluck('id')
                    ->filter();

                foreach ($data['options'] as $position => $option) {
                    $values = [
                        'content' => trim($option['content']),
                        'position' => $position,
                    ];

                    if (! empty($option['id'])) {
                        $poll->options()->whereKey($option['id'])->update($values);
                    } else {
                        $poll->options()->create($values);
                    }
                }

                // Remove omitted options only when they do not contain votes.
                $optionsToDelete = $poll->options()
                    ->whereIn('id', $existingIds)
                    ->whereNotIn('id', $submittedIds)
                    ->get();

                if (PollVote::whereIn(
                    'poll_option_id',
                    $optionsToDelete->pluck('id')
                )->exists()) {
                    throw ValidationException::withMessages([
                        'options' => 'An option with existing votes cannot be removed.',
                    ]);
                }

                if ($optionsToDelete->contains('id', $poll->correct_option_id)) {
                    $poll->update(['correct_option_id' => null]);
                }

                PollOption::whereIn('id', $optionsToDelete->pluck('id'))->delete();

                // Save the currently selected correct answer.
                $this->saveCorrectAnswer(
                    $poll,
                    $data['correct_option_index'] ?? null
                );
            });
        } catch (\Throwable $exception) {
            $this->deleteImage($newImage);
            throw $exception;
        }

        if ($newImage) {
            $this->deleteImage($oldImage);
        }

        return response()->json([
            'message' => 'Poll updated successfully.',
        ]);
    }

    /**
     * Change only Draft, Published or Closed status.
     */
    public function updateStatus(Request $request, Poll $poll)
    {
        $data = $request->validate([
            'status' => [
                'required',
                Rule::in(['draft', 'published', 'closed']),
            ],
        ]);

        $poll->update($data);

        return response()->json([
            'message' => 'Poll status updated successfully.',
        ]);
    }

    /**
     * Delete a poll and its uploaded image.
     */
    public function destroy(Poll $poll)
    {
        $image = $poll->image;
        $poll->delete();
        $this->deleteImage($image);

        return response()->json([
            'message' => 'Poll deleted successfully.',
        ]);
    }

    /**
     * Validate fields used by both create and update forms.
     */
    private function validatePoll(Request $request, ?Poll $poll = null): array
    {
        $endRules = ['nullable', 'date'];

        if ($request->filled('starts_at')) {
            $endRules[] = 'after:starts_at';
        }

        $optionIdRules = $poll
            ? [
                'nullable',
                'integer',
                Rule::exists('poll_options', 'id')
                    ->where('poll_id', $poll->id),
            ]
            : ['prohibited'];

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'poll_type' => ['required', Rule::in(['opinion', 'prediction', 'quiz'])],
            'result_visibility' => ['required', Rule::in(['always', 'after_vote', 'after_end'])],
            'allow_comments' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => $endRules,
            'status' => ['required', Rule::in(['draft', 'published', 'closed'])],
            'correct_option_index' => ['nullable', 'integer', 'min:0', 'max:19'],
            'options' => ['required', 'array', 'min:2', 'max:20'],
            'options.*.id' => $optionIdRules,
            'options.*.content' => ['required', 'string', 'max:150', 'distinct:ignore_case'],
        ]);
    }

    /**
     * Prepare the main poll table fields.
     */
    private function pollData(
        array $data,
        ?string $image,
        ?Poll $poll = null
    ): array {
        return [
            'admin_id' => $poll?->admin_id ?: Auth::guard('admin')->id(),
            'title' => trim($data['title']),
            'slug' => $this->uniqueSlug($data['title'], $poll),
            'image' => $image,
            'description' => $data['description'] ?? null,
            'poll_type' => $data['poll_type'],
            'result_visibility' => $data['result_visibility'],
            'allow_comments' => (bool) $data['allow_comments'],
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'status' => $data['status'],
        ];
    }

    /**
     * Save the correct answer selected by its visible option position.
     */
    private function saveCorrectAnswer(Poll $poll, ?int $position): void
    {
        $option = $position !== null
            ? $poll->options()->where('position', $position)->first()
            : null;

        $poll->update(['correct_option_id' => $option?->id]);
    }

    /**
     * Create a readable unique URL slug.
     */
    private function uniqueSlug(string $title, ?Poll $poll = null): string
    {
        $base = Str::slug($title) ?: 'poll';
        $slug = $base;
        $number = 2;

        while (Poll::where('slug', $slug)
            ->when($poll, fn ($query) => $query->where('id', '!=', $poll->id))
            ->exists()) {
            $slug = $base.'-'.$number++;
        }

        return $slug;
    }

    /**
     * Upload a poll image into its public directory.
     */
    private function uploadImage($image): string
    {
        $directory = public_path('admin/pollimage');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = now()->format('YmdHis')
            .'-'.Str::random(10)
            .'.'.$image->extension();

        $image->move($directory, $filename);

        return $filename;
    }

    /**
     * Delete an existing poll image safely.
     */
    private function deleteImage(?string $filename): void
    {
        if (! $filename) {
            return;
        }

        $path = public_path('admin/pollimage/'.basename($filename));

        if (is_file($path)) {
            unlink($path);
        }
    }
}
