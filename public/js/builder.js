/**
 * builder.js — Dynamic form builder driven by template JSON schema
 * Kenya Docs
 */
(function () {
    'use strict';

    const form = document.getElementById('builder-form');
    if (!form) return;

    const fieldsContainer = document.getElementById('form-fields');
    const btnPreview = document.getElementById('btn-preview');
    const slug = form.dataset.slug;
    let schema = {};

    try {
        schema = JSON.parse(form.dataset.schema || '{}');
    } catch (e) {
        console.error('Invalid schema JSON', e);
    }

    // -------------------------------------------------------------------------
    // Field renderers
    // -------------------------------------------------------------------------

    const FIELD_RENDERERS = {
        text:     renderTextInput,
        email:    renderEmailInput,
        tel:      renderTelInput,
        phone:    renderTelInput,
        number:   renderNumberInput,
        date:     renderDateInput,
        textarea: renderTextarea,
        select:   renderSelect,
        radio:    renderRadio,
        checkbox: renderCheckbox,
    };

    function renderField(field) {
        const type = field.type || 'text';
        const renderer = FIELD_RENDERERS[type] || renderTextInput;
        const wrapper = document.createElement('div');
        wrapper.className = 'form-group';
        wrapper.innerHTML = renderer(field);
        return wrapper;
    }

    function labelHtml(field) {
        const req = field.required ? '<span class="required" aria-hidden="true">*</span>' : '';
        return `<label class="form-label" for="field-${field.name}">${escHtml(field.label || field.name)}${req}</label>`;
    }

    function baseAttrs(field) {
        const req = field.required ? 'required' : '';
        const placeholder = field.placeholder ? `placeholder="${escHtml(field.placeholder)}"` : '';
        const hint = field.hint ? `aria-describedby="hint-${field.name}"` : '';
        return `id="field-${field.name}" name="${field.name}" class="form-control" ${req} ${placeholder} ${hint}`;
    }

    function hintHtml(field) {
        return field.hint ? `<span class="form-hint" id="hint-${field.name}">${escHtml(field.hint)}</span>` : '';
    }

    function renderTextInput(field) {
        return `${labelHtml(field)}<input type="text" ${baseAttrs(field)} maxlength="${field.maxlength || 500}">${hintHtml(field)}`;
    }

    function renderEmailInput(field) {
        return `${labelHtml(field)}<input type="email" ${baseAttrs(field)}>${hintHtml(field)}`;
    }

    function renderTelInput(field) {
        return `${labelHtml(field)}<input type="tel" ${baseAttrs(field)} maxlength="10" inputmode="numeric" pattern="^07\\d{8}$">${hintHtml(field)}`;
    }

    function renderNumberInput(field) {
        const min = field.min != null ? `min="${field.min}"` : '';
        const max = field.max != null ? `max="${field.max}"` : '';
        return `${labelHtml(field)}<input type="number" ${baseAttrs(field)} ${min} ${max}>${hintHtml(field)}`;
    }

    function renderDateInput(field) {
        return `${labelHtml(field)}<input type="date" ${baseAttrs(field)}>${hintHtml(field)}`;
    }

    function renderTextarea(field) {
        const rows = field.rows || 4;
        return `${labelHtml(field)}<textarea ${baseAttrs(field)} rows="${rows}" maxlength="${field.maxlength || 2000}"></textarea>${hintHtml(field)}`;
    }

    function renderSelect(field) {
        const options = (field.options || [])
            .map(opt => {
                const val = typeof opt === 'object' ? opt.value : opt;
                const lab = typeof opt === 'object' ? opt.label : opt;
                return `<option value="${escHtml(val)}">${escHtml(lab)}</option>`;
            }).join('');
        return `${labelHtml(field)}<select ${baseAttrs(field)}><option value="">— Select —</option>${options}</select>${hintHtml(field)}`;
    }

    function renderRadio(field) {
        const items = (field.options || []).map(opt => {
            const val = typeof opt === 'object' ? opt.value : opt;
            const lab = typeof opt === 'object' ? opt.label : opt;
            return `<label class="radio-label">
                <input type="radio" name="${field.name}" value="${escHtml(val)}" ${field.required ? 'required' : ''}>
                ${escHtml(lab)}
            </label>`;
        }).join('');
        return `${labelHtml(field)}<div class="radio-group" role="group">${items}</div>${hintHtml(field)}`;
    }

    function renderCheckbox(field) {
        return `<label class="checkbox-label">
            <input type="checkbox" id="field-${field.name}" name="${field.name}" value="1" ${field.required ? 'required' : ''}>
            ${escHtml(field.label || field.name)}
        </label>${hintHtml(field)}`;
    }

    // -------------------------------------------------------------------------
    // Build form from schema
    // -------------------------------------------------------------------------

    function buildForm() {
        const fields = schema.fields || schema;
        if (!Array.isArray(fields) || !fields.length) {
            fieldsContainer.innerHTML = '<p class="text-muted">No fields defined for this template.</p>';
            return;
        }

        fields.forEach(function (field) {
            fieldsContainer.appendChild(renderField(field));
        });
    }

    // -------------------------------------------------------------------------
    // Collect form data
    // -------------------------------------------------------------------------

    function collectFormData() {
        const data = {};
        const elements = form.querySelectorAll('input, select, textarea');
        elements.forEach(function (el) {
            if (!el.name || el.name === '_token') return;
            if (el.type === 'radio' && !el.checked) return;
            if (el.type === 'checkbox') {
                data[el.name] = el.checked ? '1' : '0';
                return;
            }
            data[el.name] = el.value;
        });
        return data;
    }

    // -------------------------------------------------------------------------
    // Form submit — send to preview endpoint
    // -------------------------------------------------------------------------

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        setLoading(true);

        try {
            const formData = collectFormData();
            const res = await api.post('/api/documents/' + slug + '/preview', formData);

            if (res.success) {
                // Store values in sessionStorage for preview page
                sessionStorage.setItem('doc_token', res.session_token);
                sessionStorage.setItem('doc_preview_url', res.preview_url);
                sessionStorage.setItem('doc_payment_url', res.payment_url);
                sessionStorage.setItem('doc_template_name', res.template_name);
                sessionStorage.setItem('doc_price', res.amount);
                sessionStorage.setItem('doc_edit_url', window.location.href);

                // Redirect to preview page
                window.location.href = '/preview?token=' + encodeURIComponent(res.session_token)
                    + '&preview_url=' + encodeURIComponent(res.preview_url)
                    + '&payment_url=' + encodeURIComponent(res.payment_url);
            } else {
                showToast(res.message || 'Failed to generate preview.', 'error');
            }
        } catch (err) {
            const msg = err.errors
                ? Object.values(err.errors).flat().join(' ')
                : (err.message || 'Failed to generate preview.');
            showToast(msg, 'error');
        } finally {
            setLoading(false);
        }
    });

    function setLoading(loading) {
        btnPreview.disabled = loading;
        const label = document.getElementById('btn-preview-label');
        const spinner = document.getElementById('btn-preview-spinner');
        if (label) label.classList.toggle('hidden', loading);
        if (spinner) spinner.classList.toggle('hidden', !loading);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // -------------------------------------------------------------------------
    // Initialise
    // -------------------------------------------------------------------------

    buildForm();

}());
