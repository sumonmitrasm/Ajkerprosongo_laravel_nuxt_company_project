import { copyFileSync, mkdirSync } from 'node:fs';
import { dirname } from 'node:path';

// Keep the template's local files in sync with the audited package-lock.json.
const files = {
    'sweetalert2/dist/sweetalert2.all.min.js': 'sweetalert2/sweetalert2.all.min.js',
    'sweetalert2/LICENSE': 'sweetalert2/LICENSE',
    'moment/min/moment.min.js': 'moment/moment.js',
    'moment/min/moment.min.js.map': 'moment/moment.min.js.map',
    'moment/LICENSE': 'moment/LICENSE',
    'quill/dist/quill.js': 'quill/quill.min.js',
    'quill/dist/quill.js.map': 'quill/quill.js.map',
    'quill/dist/quill.snow.css': 'quill/quill.snow.css',
    'quill/dist/quill.bubble.css': 'quill/quill.bubble.css',
    'quill/dist/quill.js.LICENSE.txt': 'quill/quill.js.LICENSE.txt',
    'quill/LICENSE': 'quill/LICENSE',
    'dompurify/dist/purify.min.js': 'dompurify/purify.min.js',
    'dompurify/dist/purify.min.js.map': 'dompurify/purify.min.js.map',
    'dompurify/LICENSE': 'dompurify/LICENSE',
};

for (const [source, target] of Object.entries(files)) {
    const destination = `public/admin/assets/plugins/${target}`;
    mkdirSync(dirname(destination), { recursive: true });
    copyFileSync(`node_modules/${source}`, destination);
}

console.log('Local template libraries updated.');
