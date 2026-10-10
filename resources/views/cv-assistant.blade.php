@extends('layouts.app')

@section('title', 'Smart CV Assistant & Resume Builder')
@section('meta-description', 'Generate ATS-optimised Kenyan CVs in minutes, or paste your existing CV for an instant score, actionable feedback, and a professionally restructured version.')

@section('content')

{{-- Page header --}}
<div style="background:linear-gradient(160deg,#0a3d12 0%,#1B5E20 100%);color:#fff;padding:3rem 0 2.5rem;">
    <div class="container" style="text-align:center;">
        <span class="badge" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);margin-bottom:1rem;font-size:.78rem;">⚡ Kenya Docs Intelligence</span>
        <h1 style="font-size:clamp(1.8rem,4vw,2.8rem);font-weight:800;color:#fff;letter-spacing:-.02em;margin-bottom:.75rem;">
            Kenyan Career &amp; CV Assistant
        </h1>
        <p style="font-size:1.05rem;color:rgba(255,255,255,.8);max-width:640px;margin:0 auto;line-height:1.65;">
            Build a job-winning, ATS-friendly CV for the Kenyan market — or audit your existing résumé for instant improvements and a professional ATS readiness score.
        </p>

        {{-- Mode tabs --}}
        <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;margin-top:2rem;">
            <button type="button" id="tab-btn-generate"
                    onclick="switchCvTab('generate')"
                    class="btn btn-accent btn-lg cv-tab-active"
                    style="min-width:200px;">
                ✨ &nbsp;Generate New CV
            </button>
            <button type="button" id="tab-btn-analyze"
                    onclick="switchCvTab('analyze')"
                    class="btn btn-outline-white btn-lg"
                    style="min-width:200px;">
                🔍 &nbsp;Audit My Existing CV
            </button>
        </div>
    </div>
</div>

<div class="section" style="background:var(--bg);padding-top:2.5rem;">
<div class="container">

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- PANEL 1 — GENERATE                                         --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div id="cv-panel-generate" style="max-width:900px;margin:0 auto;">

    <div class="card" style="border-radius:16px;">
        <div class="card-body" style="padding:clamp(1.5rem,4vw,2.5rem);">
            <h2 style="font-size:1.35rem;font-weight:800;color:var(--primary);margin-bottom:.35rem;">CV Builder Wizard</h2>
            <p style="font-size:.9rem;color:var(--text-muted);margin-bottom:2rem;">
                Fill in your details below to generate an ATS-standard Kenyan CV formatted for corporate, NGO and government applications.
            </p>

            <form id="cv-generate-form">
                @csrf

                {{-- Section 1: Personal --}}
                <div class="form-section-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    1 &nbsp;Personal &amp; Contact Information
                </div>
                <div class="grid grid-2" style="gap:1.1rem;">
                    <div class="form-group">
                        <label class="form-label" for="gen-name">Full Name *</label>
                        <input type="text" id="gen-name" name="full_name" class="form-control"
                               placeholder="e.g. Brian Kiprop Mwangi" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="gen-title">Target Job Title *</label>
                        <input type="text" id="gen-title" name="job_title" class="form-control"
                               placeholder="e.g. Senior Accountant / Logistics Officer" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="gen-phone">Phone (Kenyan format) *</label>
                        <input type="tel" id="gen-phone" name="phone" class="form-control"
                               placeholder="07XXXXXXXX or +254XXXXXXXXX" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="gen-email">Email Address *</label>
                        <input type="email" id="gen-email" name="email" class="form-control"
                               placeholder="name@example.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="gen-loc">Location / Town *</label>
                        <input type="text" id="gen-loc" name="location" class="form-control"
                               placeholder="e.g. Nairobi, Kenya" value="Nairobi, Kenya">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="gen-linkedin">LinkedIn / Portfolio URL</label>
                        <input type="url" id="gen-linkedin" name="linkedin" class="form-control"
                               placeholder="https://linkedin.com/in/username">
                    </div>
                </div>

                {{-- Section 2: Summary --}}
                <div class="form-section-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    2 &nbsp;Professional Summary
                </div>
                <div class="form-group">
                    <label class="form-label" for="gen-summary">Career Summary / Objective *</label>
                    <textarea id="gen-summary" name="summary" class="form-control" rows="4"
                              placeholder="Dedicated and results-driven professional with 4+ years of experience in financial reporting, auditing, and tax compliance. Proven track record of delivering measurable results in fast-paced environments."
                              required></textarea>
                    <span class="form-hint">40–80 words recommended. Highlight your years of experience, expertise and value proposition.</span>
                </div>

                {{-- Section 3: Experience --}}
                <div class="form-section-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    3 &nbsp;Work Experience
                </div>
                <div class="form-group">
                    <label class="form-label" for="gen-exp">Job History &amp; Key Accomplishments</label>
                    <textarea id="gen-exp" name="experience" class="form-control" rows="6"
                              placeholder="Operations Manager | ABC Logistics Ltd, Nairobi (Jan 2021 – Present)&#10;• Spearheaded supply chain workflows, reducing delivery delays by 35%.&#10;• Managed a departmental budget of KSh 12M with zero audit queries.&#10;• Trained a team of 12 field officers on compliance standards.&#10;&#10;Junior Officer | XYZ Enterprise (2018 – 2020)&#10;• Coordinated customer logistics and daily stock reconciliations."></textarea>
                    <span class="form-hint">Use bullet points. Start each bullet with an action verb (Managed, Developed, Spearheaded…).</span>
                </div>

                {{-- Section 4: Education --}}
                <div class="form-section-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                    4 &nbsp;Education &amp; Academic Background
                </div>
                <div class="form-group">
                    <label class="form-label" for="gen-edu">Qualifications &amp; Institutions *</label>
                    <textarea id="gen-edu" name="education" class="form-control" rows="3"
                              placeholder="Bachelor of Commerce (Finance Option) | University of Nairobi (2015 – 2019)&#10;KCSE Grade B+ | Alliance High School (2010 – 2014)"
                              required></textarea>
                </div>

                {{-- Section 5: Skills --}}
                <div class="form-section-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    5 &nbsp;Skills, Certifications &amp; Languages
                </div>
                <div class="grid grid-2" style="gap:1.1rem;">
                    <div class="form-group">
                        <label class="form-label" for="gen-skills">Core Skills (comma-separated) *</label>
                        <input type="text" id="gen-skills" name="skills" class="form-control"
                               placeholder="Budgeting, QuickBooks, Data Analysis, Team Leadership" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="gen-cert">Professional Certifications</label>
                        <input type="text" id="gen-cert" name="certifications" class="form-control"
                               placeholder="CPA (K), CIFA, Certified Scrum Master">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="gen-lang">Languages</label>
                        <input type="text" id="gen-lang" name="languages" class="form-control"
                               placeholder="English (Fluent), Kiswahili (Fluent)">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="gen-ref">Referees</label>
                        <input type="text" id="gen-ref" name="references" class="form-control"
                               placeholder="Available upon request">
                    </div>
                </div>

                <div style="text-align:center;margin-top:2rem;">
                    <button type="submit" class="btn btn-accent btn-xl" id="btn-submit-generate" style="min-width:260px;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Generate Professional CV
                    </button>
                </div>
            </form>

            {{-- Generated CV output --}}
            <div id="gen-result-container" class="hidden" style="margin-top:2.5rem;border-top:2px dashed var(--border);padding-top:2rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem;">
                    <h3 style="font-size:1.2rem;font-weight:800;color:var(--primary);margin:0;">Your Generated CV</h3>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        <button type="button" class="btn btn-ghost btn-sm" onclick="copyCvText('gen-output-text')">
                            📋 Copy Text
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="downloadCvAsFile('gen-output-text','Kenya_CV.txt')">
                            💾 Download .TXT
                        </button>
                    </div>
                </div>
                <div id="gen-output-html"
                     style="background:#fff;border:1px solid var(--border);border-radius:12px;padding:1rem;margin-bottom:1rem;overflow-x:auto;">
                </div>
                <details>
                    <summary style="font-size:.85rem;color:var(--text-muted);cursor:pointer;margin-bottom:.5rem;">View Plain Text Version</summary>
                    <textarea id="gen-output-text" class="form-control" rows="12" readonly
                              style="font-family:monospace;font-size:.85rem;background:#fafafa;"></textarea>
                </details>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- PANEL 2 — AUDIT                                            --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div id="cv-panel-analyze" class="hidden" style="max-width:900px;margin:0 auto;">

    <div class="card" style="border-radius:16px;">
        <div class="card-body" style="padding:clamp(1.5rem,4vw,2.5rem);">
            <h2 style="font-size:1.35rem;font-weight:800;color:var(--primary);margin-bottom:.35rem;">CV Audit &amp; Enhancer</h2>
            <p style="font-size:.9rem;color:var(--text-muted);margin-bottom:2rem;">
                Paste your CV text below to receive an instant ATS readiness score, specific actionable feedback, and a professionally restructured version ready to submit.
            </p>

            {{-- File upload info --}}
            <div class="alert alert-info" style="margin-bottom:1.5rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <strong>Best results: paste your CV text.</strong>
                    Plain .txt files can also be uploaded. PDF and Word uploads are not supported — please copy &amp; paste the text content instead for accurate analysis.
                </div>
            </div>

            <form id="cv-analyze-form">
                @csrf
                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label" for="audit-file">Upload Plain Text CV (.txt only)</label>
                    <input type="file" id="audit-file" name="cv_file" class="form-control" accept=".txt">
                </div>

                <div class="form-group">
                    <label class="form-label" for="audit-text">Or Paste Your Full CV Text *</label>
                    <textarea id="audit-text" name="cv_text" class="form-control" rows="10"
                              placeholder="Paste your complete CV or résumé text here…&#10;&#10;Include your name, contact details, summary, work experience, education, skills and referees for the most accurate audit."></textarea>
                </div>

                <div style="text-align:center;margin-top:1.5rem;">
                    <button type="submit" class="btn btn-accent btn-xl" id="btn-submit-analyze" style="min-width:260px;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        Audit &amp; Improve My CV
                    </button>
                </div>
            </form>

            {{-- Audit results --}}
            <div id="audit-results-container" class="hidden" style="margin-top:2.5rem;border-top:2px dashed var(--border);padding-top:2rem;">

                {{-- Score card --}}
                <div style="background:#fff;border:1px solid var(--border);border-radius:14px;padding:1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.25rem;margin-bottom:1.5rem;box-shadow:var(--shadow);">
                    <div>
                        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:.35rem;">ATS Readiness Score</div>
                        <div id="audit-rating-title" style="font-size:1.75rem;font-weight:800;color:var(--primary);line-height:1;margin-bottom:.3rem;">—</div>
                        <div id="audit-word-count" style="font-size:.85rem;color:var(--text-muted);">Word count: —</div>
                    </div>
                    <div id="audit-score-circle"
                         style="width:88px;height:88px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2.1rem;font-weight:800;box-shadow:0 4px 16px rgba(27,94,32,.3);flex-shrink:0;">
                        —
                    </div>
                </div>

                {{-- Issues & suggestions --}}
                <div class="grid grid-2" style="gap:1.25rem;margin-bottom:1.5rem;">
                    <div style="background:#FEF2F2;border:1.5px solid #FECACA;border-radius:12px;padding:1.25rem;">
                        <h4 style="color:#991B1B;font-size:1rem;font-weight:700;margin-bottom:.75rem;display:flex;align-items:center;gap:.4rem;">
                            <span>⚠️</span> Issues Found
                        </h4>
                        <div id="audit-issues-list" style="font-size:.875rem;display:flex;flex-direction:column;gap:.65rem;"></div>
                    </div>
                    <div style="background:#FFFBEB;border:1.5px solid #FDE68A;border-radius:12px;padding:1.25rem;">
                        <h4 style="color:#92400E;font-size:1rem;font-weight:700;margin-bottom:.75rem;display:flex;align-items:center;gap:.4rem;">
                            <span>💡</span> Improvements
                        </h4>
                        <div id="audit-suggestions-list" style="font-size:.875rem;display:flex;flex-direction:column;gap:.65rem;"></div>
                    </div>
                </div>

                {{-- Positives --}}
                <div id="audit-positives-wrap" style="background:#F0FDF4;border:1.5px solid #A7F3D0;border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;display:none;">
                    <h4 style="color:#065F46;font-size:1rem;font-weight:700;margin-bottom:.75rem;display:flex;align-items:center;gap:.4rem;">
                        <span>✅</span> Strengths
                    </h4>
                    <div id="audit-positives-list" style="font-size:.875rem;display:flex;flex-direction:column;gap:.5rem;"></div>
                </div>

                {{-- Enhanced output --}}
                <div style="background:#F8FFF9;border:1.5px solid #A7F3D0;border-radius:12px;padding:1.5rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:.75rem;">
                        <h4 style="color:#065F46;font-size:1.1rem;font-weight:700;margin:0;">✨ Auto-Enhanced Version</h4>
                        <div style="display:flex;gap:.5rem;">
                            <button type="button" class="btn btn-ghost btn-sm" onclick="copyCvText('audit-enhanced-text')">📋 Copy</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="downloadCvAsFile('audit-enhanced-text','Enhanced_CV.txt')">💾 Download</button>
                        </div>
                    </div>
                    <p style="font-size:.82rem;color:#047857;margin-bottom:.75rem;line-height:1.5;">
                        Section headers normalised, passive phrases replaced with active verbs, bullet formatting standardised. Remove the notes block at the bottom before submitting.
                    </p>
                    <textarea id="audit-enhanced-text" class="form-control" rows="14" readonly
                              style="font-family:monospace;font-size:.85rem;background:#fff;"></textarea>
                </div>

            </div>
        </div>
    </div>
</div>

</div>
</div>

@push('scripts')
<script>
// ── Tab switcher ───────────────────────────────────────────────
function switchCvTab(tab) {
    const isGen = tab === 'generate';
    const btnGen = document.getElementById('tab-btn-generate');
    const btnAna = document.getElementById('tab-btn-analyze');
    const pnlGen = document.getElementById('cv-panel-generate');
    const pnlAna = document.getElementById('cv-panel-analyze');

    if (isGen) {
        btnGen.className = 'btn btn-accent btn-lg cv-tab-active';
        btnAna.className = 'btn btn-outline-white btn-lg';
        pnlGen.classList.remove('hidden');
        pnlAna.classList.add('hidden');
    } else {
        btnAna.className = 'btn btn-accent btn-lg cv-tab-active';
        btnGen.className = 'btn btn-outline-white btn-lg';
        pnlAna.classList.remove('hidden');
        pnlGen.classList.add('hidden');
    }
}

// ── Generate CV ───────────────────────────────────────────────
document.getElementById('cv-generate-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-generate');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="width:18px;height:18px;border-width:2px;border-top-color:#fff;"></span> &nbsp;Generating…';

    const data = Object.fromEntries(new FormData(this).entries());
    try {
        const res = await api.post('/api/cv/generate', data);
        if (res.success) {
            document.getElementById('gen-output-text').value = res.cv_text;
            document.getElementById('gen-output-html').innerHTML = res.cv_html;
            document.getElementById('gen-result-container').classList.remove('hidden');
            showToast('CV generated successfully!', 'success');
            document.getElementById('gen-result-container').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            showToast(res.message || 'Generation failed. Please check your inputs.', 'error');
        }
    } catch (err) {
        showToast(err.message || 'An error occurred. Please try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = orig;
    }
});

// ── Audit CV ──────────────────────────────────────────────────
document.getElementById('cv-analyze-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-analyze');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="width:18px;height:18px;border-width:2px;border-top-color:#fff;"></span> &nbsp;Analysing…';

    const formData = new FormData(this);
    try {
        const response = await fetch('/api/cv/analyze', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();

        if (data.success) {
            renderAuditResults(data);
            showToast('CV audit complete!', 'success');
            document.getElementById('audit-results-container').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            showToast(data.message || 'Audit failed. Please try again.', 'error');
        }
    } catch (err) {
        showToast(err.message || 'An error occurred during analysis.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = orig;
    }
});

function renderAuditResults(data) {
    const a = data.analysis;

    // Score circle
    const circle = document.getElementById('audit-score-circle');
    circle.textContent = a.score;
    circle.style.background = a.color || (a.score >= 70 ? '#16a34a' : a.score >= 50 ? '#d97706' : '#dc2626');

    document.getElementById('audit-rating-title').textContent = a.rating;
    document.getElementById('audit-word-count').textContent = 'Word count: ' + a.word_count + ' words';

    // Issues
    const issEl = document.getElementById('audit-issues-list');
    issEl.innerHTML = '';
    if (a.issues.length === 0) {
        issEl.innerHTML = '<div style="color:#166534;font-weight:600;">✓ No critical issues found!</div>';
    } else {
        a.issues.forEach(function (iss) {
            issEl.innerHTML += '<div style="border-left:3px solid #FCA5A5;padding-left:.6rem;"><strong>' + esc(iss.title) + '</strong><br><span style="color:var(--text-muted);">' + esc(iss.description) + '</span></div>';
        });
    }

    // Suggestions
    const sugEl = document.getElementById('audit-suggestions-list');
    sugEl.innerHTML = '';
    if (a.suggestions.length === 0) {
        sugEl.innerHTML = '<div style="color:#166534;font-weight:600;">✓ Great job! Following best practices.</div>';
    } else {
        a.suggestions.forEach(function (s) {
            sugEl.innerHTML += '<div style="border-left:3px solid #FDE68A;padding-left:.6rem;"><strong>' + esc(s.title) + '</strong><br><span style="color:var(--text-muted);">' + esc(s.description) + '</span></div>';
        });
    }

    // Positives
    const posWrap = document.getElementById('audit-positives-wrap');
    const posEl   = document.getElementById('audit-positives-list');
    posEl.innerHTML = '';
    if (a.positives && a.positives.length > 0) {
        posWrap.style.display = 'block';
        a.positives.forEach(function (p) {
            posEl.innerHTML += '<div style="display:flex;align-items:flex-start;gap:.4rem;"><span style="color:#16a34a;flex-shrink:0;">✓</span><span>' + esc(p) + '</span></div>';
        });
    } else {
        posWrap.style.display = 'none';
    }

    // Enhanced CV
    document.getElementById('audit-enhanced-text').value = data.modified_cv;
    document.getElementById('audit-results-container').classList.remove('hidden');
}

// ── Utilities ─────────────────────────────────────────────────
function esc(str) {
    const d = document.createElement('div');
    d.innerText = String(str || '');
    return d.innerHTML;
}

function copyCvText(id) {
    const el = document.getElementById(id);
    el.select();
    navigator.clipboard.writeText(el.value)
        .then(function () { showToast('Copied to clipboard!', 'success'); })
        .catch(function () { document.execCommand('copy'); showToast('Copied!', 'success'); });
}

function downloadCvAsFile(id, filename) {
    const text = document.getElementById(id).value;
    const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endpush
@endsection
