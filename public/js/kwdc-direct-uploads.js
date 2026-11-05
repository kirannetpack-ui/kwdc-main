(() => {
    const stageUrl = document.querySelector('meta[name="kwdc-upload-stage"]')?.content;
    if (!stageUrl) return;

    const submitting = new WeakSet();
    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (event.defaultPrevented || !(form instanceof HTMLFormElement) || form.method !== 'post') return;
        const inputs = Array.from(form.elements).filter(input => input instanceof HTMLInputElement
            && input.type === 'file' && input.name && !input.matches(':disabled') && input.files.length);
        if (!inputs.length) return;
        event.preventDefault();
        if (submitting.has(form)) return;
        submitting.add(form);

        const button = event.submitter;
        const status = document.createElement('p');
        status.setAttribute('role', 'status');
        status.textContent = 'Uploading your files…';
        form.prepend(status);
        if (button) button.disabled = true;

        try {
            const tokens = [];
            const indexes = new Map();
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            for (const input of inputs) {
                for (const file of input.files) {
                    const field = input.name.replace(/\[([^\]]*)\]/g, (_, index) => {
                        if (index !== '') return '.' + index;
                        const next = indexes.get(input.name) ?? 0;
                        indexes.set(input.name, next + 1);
                        return '.' + next;
                    });
                    const signed = await fetch(stageUrl, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf},
                        body: JSON.stringify({field, name: file.name, size: file.size}),
                    });
                    if (!signed.ok) throw new Error('Unable to prepare the upload. Check the file size and try again.');
                    const {url, headers, token} = await signed.json();
                    const putHeaders = new Headers();
                    for (const [name, values] of Object.entries(headers)) {
                        if (!['host', 'content-length'].includes(name.toLowerCase())) {
                            putHeaders.set(name, values.join(', '));
                        }
                    }
                    const uploaded = await fetch(url, {method: 'PUT', headers: putHeaders, body: file});
                    if (!uploaded.ok) throw new Error('A file could not be uploaded. Please try again.');
                    tokens.push(token);
                }
            }
            const references = document.createElement('input');
            references.type = 'hidden';
            references.name = '_direct_uploads';
            references.value = JSON.stringify(tokens);
            form.append(references);
            if (button?.name) {
                const submitter = document.createElement('input');
                submitter.type = 'hidden';
                submitter.name = button.name;
                submitter.value = button.value;
                form.append(submitter);
            }
            inputs.forEach(input => { input.disabled = true; });
            status.textContent = 'Saving your changes…';
            HTMLFormElement.prototype.submit.call(form);
        } catch (error) {
            status.textContent = error.message || 'Unable to upload files. Please try again.';
            submitting.delete(form);
            if (button) button.disabled = false;
        }
    });
})();
