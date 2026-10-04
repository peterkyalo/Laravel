/**
 * Universal Rich Text Editor & Live Image Preview System
 * Antigravity Conservatory LMS
 */

document.addEventListener('DOMContentLoaded', function () {
    initRichTextEditors();
    initImagePreviews();
});

/**
 * Initialize Quill Rich Text Editor on all descriptive textareas
 */
function initRichTextEditors() {
    if (typeof Quill === 'undefined') return;

    // Selector targeting all description, body, syllabus, instructions, and rich textareas
    const rawElements = document.querySelectorAll(
        'textarea.richtext, textarea.rich-text-editor, textarea.rich-editor, textarea[name="description"], textarea[name*="description"], textarea[name="body"], textarea[name="content"], textarea[name*="_content"], textarea[name="syllabus"], textarea[name="instructions"], textarea[name*="_letter"], textarea[data-editor="rich"]'
    );

    const textareas = Array.from(rawElements).filter(function (ta) {
        return ta.name !== 'meta_description' && !ta.classList.contains('no-rich');
    });

    textareas.forEach(function (textarea) {
        // Prevent duplicate initialization
        if (textarea.dataset.quillInitialized === 'true') return;
        textarea.dataset.quillInitialized = 'true';

        // Hide original textarea
        textarea.style.display = 'none';

        // Create container for Quill
        const editorWrapper = document.createElement('div');
        editorWrapper.className = 'quill-universal-wrapper mb-3';

        const quillContainer = document.createElement('div');
        quillContainer.className = 'quill-editor-instance';
        quillContainer.innerHTML = textarea.value || '';

        editorWrapper.appendChild(quillContainer);
        textarea.parentNode.insertBefore(editorWrapper, textarea.nextSibling);

        // Initialize Quill with comprehensive formatting options and inline image upload
        const quill = new Quill(quillContainer, {
            theme: 'snow',
            modules: {
                toolbar: {
                    container: [
                        [{ header: [1, 2, 3, 4, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ color: [] }, { background: [] }],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['blockquote', 'code-block'],
                        ['link', 'image', 'clean']
                    ],
                    handlers: {
                        image: function () {
                            selectLocalImage(quill);
                        }
                    }
                }
            }
        });

        // Safely handle required attribute to avoid browser hidden control validation error
        const wasRequired = textarea.required;
        if (wasRequired) {
            textarea.required = false;
        }

        function getCleanHTML() {
            let htmlVal = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
            if (htmlVal) {
                htmlVal = htmlVal.replace(/<span class="ql-ui"[^>]*><\/span>/gi, '');
            }
            return htmlVal;
        }

        // Sync Quill HTML back to hidden textarea on change
        quill.on('text-change', function () {
            textarea.value = getCleanHTML();
        });

        // Ensure sync before parent form submission
        const form = textarea.closest('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                const htmlVal = getCleanHTML();
                textarea.value = htmlVal;
                if (wasRequired && (!htmlVal || quill.getText().trim() === '')) {
                    e.preventDefault();
                    quill.focus();
                    quillContainer.style.borderColor = '#ef4444';
                    alert('Please enter content before submitting.');
                }
            });
        }
    });
}

/**
 * Handle local image selection and AJAX upload for inline rich text insertion
 */
function selectLocalImage(quill) {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();

    input.onchange = function () {
        const file = input.files && input.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            alert('Please select a valid image file (JPG, PNG, WEBP, GIF).');
            return;
        }

        const range = quill.getSelection(true) || { index: quill.getLength() };
        const cursorIndex = range.index;

        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.content : '';

        if (csrfToken) {
            const formData = new FormData();
            formData.append('image', file);

            fetch('/media/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.url) {
                    quill.insertEmbed(cursorIndex, 'image', data.url);
                    quill.setSelection(cursorIndex + 1);
                } else {
                    insertBase64(file, quill, cursorIndex);
                }
            })
            .catch(() => {
                insertBase64(file, quill, cursorIndex);
            });
        } else {
            insertBase64(file, quill, cursorIndex);
        }
    };
}

function insertBase64(file, quill, index) {
    const reader = new FileReader();
    reader.onload = function (e) {
        quill.insertEmbed(index, 'image', e.target.result);
        quill.setSelection(index + 1);
    };
    reader.readAsDataURL(file);
}

/**
 * Universal Live Image Preview before uploading or saving
 */
function initImagePreviews() {
    // Target any file input accepting images or named with image/artwork/avatar/logo/cover
    const fileInputs = document.querySelectorAll(
        'input[type="file"][accept*="image"], input[type="file"].image-preview-input, input[type="file"][name*="image"], input[type="file"][name*="artwork"], input[type="file"][name*="cover"], input[type="file"][name*="avatar"], input[type="file"][name*="logo"], input[type="file"][name*="favicon"]'
    );

    fileInputs.forEach(function (input) {
        if (input.dataset.previewInitialized === 'true') return;
        input.dataset.previewInitialized = 'true';

        // Find or create preview container
        let previewBox = null;
        if (input.dataset.previewTarget) {
            previewBox = document.querySelector(input.dataset.previewTarget);
        }

        if (!previewBox) {
            previewBox = document.createElement('div');
            previewBox.className = 'universal-image-preview mt-2 d-none';
            input.parentNode.appendChild(previewBox);
        }

        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) {
                previewBox.classList.add('d-none');
                return;
            }

            // Verify file is an image
            if (!file.type.startsWith('image/')) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                const fileSizeFormatted = file.size > 1048576 
                    ? (file.size / 1048576).toFixed(2) + ' MB' 
                    : (file.size / 1024).toFixed(1) + ' KB';

                // Check if preview box already has custom structure
                const existingImg = previewBox.querySelector('.preview-img');
                const existingInfo = previewBox.querySelector('.preview-info');

                if (existingImg) {
                    existingImg.src = e.target.result;
                    if (existingInfo) {
                        existingInfo.textContent = file.name + ' (' + fileSizeFormatted + ')';
                    }
                    previewBox.classList.remove('d-none');
                } else {
                    // Render default responsive preview card
                    previewBox.innerHTML = `
                        <div class="p-3 rounded-3 bg-surface-elevated border border-gold d-inline-flex flex-column align-items-start gap-2 shadow-sm">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-gold text-dark small fw-bold"><i class="bi bi-eye-fill me-1"></i> Live Image Preview</span>
                                <span class="text-white small fw-semibold text-truncate" style="max-width: 250px;">${file.name}</span>
                                <span class="badge bg-surface text-gold border border-secondary small">${fileSizeFormatted}</span>
                            </div>
                            <div class="position-relative">
                                <img src="${e.target.result}" alt="Preview" class="rounded border border-secondary" style="max-height: 220px; max-width: 100%; object-fit: contain; background: #070a10;">
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <small class="text-muted" style="font-size: 0.72rem;"><i class="bi bi-check-circle text-success me-1"></i> Ready to upload on save</small>
                                <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 cancel-preview-btn ms-2" style="font-size: 0.75rem;">
                                    <i class="bi bi-x-circle me-1"></i> Cancel Selection
                                </button>
                            </div>
                        </div>
                    `;
                    previewBox.classList.remove('d-none');
                }

                // Bind Cancel Selection button
                const cancelBtn = previewBox.querySelector('.cancel-preview-btn');
                if (cancelBtn) {
                    cancelBtn.onclick = function (ev) {
                        ev.preventDefault();
                        input.value = '';
                        previewBox.classList.add('d-none');
                    };
                }
            };

            reader.readAsDataURL(file);
        });
    });
}
