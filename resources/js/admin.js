const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

/**
 * Rich-text editors: <div data-editor data-input="field_id"> mirrors HTML into a hidden input.
 * Quill and its stylesheet are fetched only on pages that actually have an editor.
 */
async function initEditors() {
    const editors = document.querySelectorAll('[data-editor]');
    if (!editors.length) return;

    const [{ default: Quill }] = await Promise.all([import('quill'), import('quill/dist/quill.snow.css')]);

    editors.forEach((el) => {
        const input = document.getElementById(el.dataset.input);
        const quill = new Quill(el, {
            theme: 'snow',
            placeholder: 'Tulis konten di sini…',
            modules: {
                toolbar: {
                    container: [
                        [{ header: [2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['blockquote', 'link', 'image'],
                        [{ align: [] }],
                        ['clean'],
                    ],
                    handlers: { image: () => uploadImage(quill) },
                },
            },
        });

        quill.root.innerHTML = input.value;
        quill.on('text-change', () => {
            input.value = quill.getText().trim() ? quill.root.innerHTML : '';
        });
    });
}

function uploadImage(quill) {
    const picker = Object.assign(document.createElement('input'), { type: 'file', accept: 'image/*' });
    picker.addEventListener('change', async () => {
        const body = new FormData();
        body.append('image', picker.files[0]);
        const response = await fetch('/admin/editor-upload', { method: 'POST', body, headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });
        if (!response.ok) return alert('Gagal mengunggah gambar.');
        const { url } = await response.json();
        const range = quill.getSelection(true);
        quill.insertEmbed(range.index, 'image', url);
    });
    picker.click();
}

/** Live preview for image inputs and file names for file inputs. */
function initUploads() {
    document.querySelectorAll('[data-upload]').forEach((wrap) => {
        const input = wrap.querySelector('input[type=file]');
        const preview = wrap.querySelector('[data-upload-preview]');
        const name = wrap.querySelector('[data-upload-name]');

        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;
            if (name) name.textContent = file.name;
            if (preview && file.type.startsWith('image/')) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
                wrap.querySelector('[data-upload-empty]')?.classList.add('hidden');
            }
        });
    });
}

/** Icon picker: clicking a swatch writes its name into the target input. */
function initIconPickers() {
    document.querySelectorAll('[data-icon-picker]').forEach((picker) => {
        const input = document.getElementById(picker.dataset.iconPicker);
        const current = picker.closest('[data-icon-field]').querySelector('[data-icon-current]');
        const highlight = () =>
            picker.querySelectorAll('[data-icon]').forEach((b) => b.classList.toggle('!border-primary', b.dataset.icon === input.value));

        picker.querySelectorAll('[data-icon]').forEach((button) =>
            button.addEventListener('click', () => {
                input.value = button.dataset.icon;
                current.innerHTML = button.innerHTML;
                highlight();
            }),
        );
        highlight();
    });
}

function initSidebar() {
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const toggle = (open) => {
        sidebar.classList.toggle('-translate-x-full', !open);
        backdrop.classList.toggle('hidden', !open);
    };
    document.querySelectorAll('[data-sidebar-toggle]').forEach((b) => b.addEventListener('click', () => toggle(sidebar.classList.contains('-translate-x-full'))));
    backdrop?.addEventListener('click', () => toggle(false));

    // Keep the active menu item in view.
    sidebar?.querySelector('.menu-item.is-active')?.scrollIntoView({ block: 'center' });
}

function initConfirm() {
    document.querySelectorAll('form[data-confirm]').forEach((form) =>
        form.addEventListener('submit', (e) => {
            if (!confirm(form.dataset.confirm)) e.preventDefault();
        }),
    );
}

function initToasts() {
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        setTimeout(() => {
            toast.style.transition = 'opacity .4s, transform .4s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            setTimeout(() => toast.remove(), 400);
        }, 4000);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initEditors();
    initUploads();
    initIconPickers();
    initSidebar();
    initConfirm();
    initToasts();
});
