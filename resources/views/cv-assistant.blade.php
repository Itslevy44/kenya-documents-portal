@extends('layouts.app')

@section('title', 'Smart CV Assistant & Resume Builder')
@section('meta-description', 'Generate ATS-compliant Kenyan CVs in minutes, or upload your existing CV for automated audit, score analysis, and instant improvements.')

@section('content')
<section class="section py-4" style="background: linear-gradient(180deg, #F0FDF4 0%, var(--bg, #F4F6F4) 100%);">
    <div class="container">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <span class="badge badge-accent mb-2">⚡ Powered by KenyaDocs Intelligence</span>
            <h1 class="section-title" style="font-size: clamp(1.8rem, 3.5vw, 2.6rem); font-weight: 800; color: var(--primary, #1B5E20);">
                Kenyan Career &amp; CV Assistant
            </h1>
            <p class="section-subtitle" style="max-width: 680px; margin: 0 auto; font-size: 1.05rem;">
                Craft a job-winning, ATS-friendly curriculum vitae tailored for the Kenyan job market, or audit your existing CV for instant improvements and recruiter-ready scoring.
            </p>
        </div>

        <!-- Mode Tabs -->
        <div class="cv-tab-container mb-4" style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
            <button type="button" class="btn btn-primary cv-tab-btn active" id="tab-btn-generate" onclick="switchCvTab('generate')">
                ✨ Generate New CV
            </button>
            <button type="button" class="btn btn-outline cv-tab-btn" id="tab-btn-analyze" onclick="switchCvTab('analyze')">
                🔍 Audit &amp; Modify My Existing CV
            </button>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: GENERATE CV                         -->
        <!-- ========================================== -->
        <div id="cv-panel-generate" class="card shadow-lg" style="max-width: 980px; margin: 0 auto; border-radius: 16px; border: 1px solid var(--border);">
            <div class="card-body" style="padding: clamp(1.5rem, 3vw, 2.5rem);">
                <div style="border-bottom: 2px solid var(--border); padding-bottom: 1rem; margin-bottom: 2rem;">
                    <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--primary);">CV Builder Wizard</h2>
                    <p class="text-muted" style="font-size: .95rem; margin: 0;">Fill in your details to create an ATS-standard Kenyan CV formatted for corporate, NGO, and government applications.</p>
                </div>

                <form id="cv-generate-form" onsubmit="handleCvGenerate(event)">
                    @csrf
                    <!-- Personal Info -->
                    <h3 style="font-size: 1.15rem; font-weight: 600; color: var(--text); margin-bottom: 1rem;">1. Personal &amp; Contact Information</h3>
                    <div class="grid grid-2" style="gap: 1.25rem;">
                        <div class="form-group">
                            <label class="form-label" for="gen-name">Full Name *</label>
                            <input type="text" id="gen-name" name="full_name" class="form-control" placeholder="e.g. Brian Kiprop Mwangi" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="gen-title">Target Job Title *</label>
                            <input type="text" id="gen-title" name="job_title" class="form-control" placeholder="e.g. Senior Accountant / Logistics Officer" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="gen-phone">Phone Number (Kenyan Format) *</label>
                            <input type="tel" id="gen-phone" name="phone" class="form-control" placeholder="07XXXXXXXX or +254XXXXXXXX" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="gen-email">Email Address *</label>
                            <input type="email" id="gen-email" name="email" class="form-control" placeholder="name@example.com" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="gen-loc">Location / Town *</label>
                            <input type="text" id="gen-loc" name="location" class="form-control" placeholder="e.g. Nairobi, Kenya" value="Nairobi, Kenya" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="gen-linkedin">LinkedIn / Portfolio URL (Optional)</label>
                            <input type="url" id="gen-linkedin" name="linkedin" class="form-control" placeholder="https://linkedin.com/in/username">
                        </div>
                    </div>

                    <!-- Professional Summary -->
                    <h3 style="font-size: 1.15rem; font-weight: 600; color: var(--text); margin-top: 1.5rem; margin-bottom: 1rem;">2. Professional Summary</h3>
                    <div class="form-group">
                        <label class="form-label" for="gen-summary">Career Summary / Objective *</label>
                        <textarea id="gen-summary" name="summary" class="form-control" rows="3" placeholder="Dedicated and results-driven professional with 4+ years of experience in financial reporting, auditing, and tax compliance..." required></textarea>
                        <small class="form-hint" style="color: var(--text-muted); font-size: .82rem;">Highlight your years of experience, core expertise, and value proposition.</small>
                    </div>

                    <!-- Work Experience -->
                    <h3 style="font-size: 1.15rem; font-weight: 600; color: var(--text); margin-top: 1.5rem; margin-bottom: 1rem;">3. Work Experience</h3>
                    <div class="form-group">
                        <label class="form-label" for="gen-exp">Job History &amp; Key Accomplishments</label>
                        <textarea id="gen-exp" name="experience" class="form-control" rows="5" placeholder="Operations Manager | ABC Logistics Ltd, Nairobi (Jan 2021 – Present)&#10;• Spearheaded supply chain workflows, reducing delivery delays by 35%.&#10;• Managed a departmental budget of KSh 12M with zero audit queries.&#10;&#10;Junior Officer | XYZ Enterprise (2018 – 2020)&#10;• Coordinated customer logistics and stock reconciliations."></textarea>
                    </div>

                    <!-- Education -->
                    <h3 style="font-size: 1.15rem; font-weight: 600; color: var(--text); margin-top: 1.5rem; margin-bottom: 1rem;">4. Education &amp; Academic Background</h3>
                    <div class="form-group">
                        <label class="form-label" for="gen-edu">Qualifications &amp; Institutions *</label>
                        <textarea id="gen-edu" name="education" class="form-control" rows="3" placeholder="Bachelor of Commerce (Finance Option) | University of Nairobi (2015 – 2019)&#10;KCSE Certificate | Alliance High School (2010 – 2014)" required></textarea>
                    </div>

                    <!-- Skills & Certifications -->
                    <h3 style="font-size: 1.15rem; font-weight: 600; color: var(--text); margin-top: 1.5rem; margin-bottom: 1rem;">5. Core Skills &amp; Certifications</h3>
                    <div class="grid grid-2" style="gap: 1.25rem;">
                        <div class="form-group">
                            <label class="form-label" for="gen-skills">Skills (Comma-separated) *</label>
                            <input type="text" id="gen-skills" name="skills" class="form-control" placeholder="Budgeting, QuickBooks, Data Analysis, Team Leadership" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="gen-cert">Professional Certifications</label>
                            <input type="text" id="gen-cert" name="certifications" class="form-control" placeholder="CPA (K), CIFA, Certified Scrum Master">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="gen-lang">Languages</label>
                            <input type="text" id="gen-lang" name="languages" class="form-control" placeholder="English (Fluent), Kiswahili (Fluent)">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="gen-ref">Referees</label>
                            <input type="text" id="gen-ref" name="references" class="form-control" placeholder="Available upon request or list 2-3 referees">
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <button type="submit" class="btn btn-accent btn-lg" id="btn-submit-generate" style="min-width: 240px;">
                            🚀 Generate Professional CV
                        </button>
                    </div>
                </form>

                <!-- CV Result Container -->
                <div id="gen-result-container" class="mt-4 hidden" style="border-top: 2px dashed var(--border); padding-top: 2rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: .75rem;">
                        <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--primary); margin: 0;">Your Generated CV</h3>
                        <div style="display: flex; gap: .5rem;">
                            <button type="button" class="btn btn-outline btn-sm" onclick="copyCvText('gen-output-text')">📋 Copy Full Text</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="downloadCvAsFile('gen-output-text', 'Kenyan_CV.txt')">💾 Download .TXT</button>
                        </div>
                    </div>
                    <div id="gen-output-html" class="p-3 mb-3" style="background: #fff; border: 1px solid var(--border); border-radius: 8px;"></div>
                    <textarea id="gen-output-text" class="form-control" rows="12" readonly style="font-family: monospace; font-size: .9rem; background: #fafafa;"></textarea>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: AUDIT & MODIFY EXISTING CV          -->
        <!-- ========================================== -->
        <div id="cv-panel-analyze" class="card shadow-lg hidden" style="max-width: 980px; margin: 0 auto; border-radius: 16px; border: 1px solid var(--border);">
            <div class="card-body" style="padding: clamp(1.5rem, 3vw, 2.5rem);">
                <div style="border-bottom: 2px solid var(--border); padding-bottom: 1rem; margin-bottom: 2rem;">
                    <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--primary);">CV Review, Audit &amp; Enhancer</h2>
                    <p class="text-muted" style="font-size: .95rem; margin: 0;">Paste your CV text or upload a document to get an instant Kenyan ATS score, actionable feedback, and an auto-enhanced version.</p>
                </div>

                <form id="cv-analyze-form" onsubmit="handleCvAnalyze(event)">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="form-label" for="audit-file">Upload CV Document (Optional: .txt, .pdf, .docx)</label>
                        <input type="file" id="audit-file" name="cv_file" class="form-control" accept=".txt,.pdf,.docx,.doc">
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="audit-text">Or Paste CV Text Here *</label>
                        <textarea id="audit-text" name="cv_text" class="form-control" rows="8" placeholder="Paste your complete resume or CV text here to analyze..."></textarea>
                    </div>

                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-accent btn-lg" id="btn-submit-analyze" style="min-width: 260px;">
                            🔬 Audit &amp; Modify My CV Now
                        </button>
                    </div>
                </form>

                <!-- Analysis Results Section -->
                <div id="audit-results-container" class="mt-4 hidden" style="border-top: 2px dashed var(--border); padding-top: 2rem;">
                    <!-- Score Header -->
                    <div style="background: #FFFFFF; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,.06); border: 1px solid var(--border); margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <div style="font-size: .9rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-weight: 600;">Overall ATS Readiness Score</div>
                            <div id="audit-rating-title" style="font-size: 1.6rem; font-weight: 800; color: var(--primary);">Strong Profile</div>
                            <div id="audit-word-count" style="font-size: .85rem; color: var(--text-muted);">Word Count: 420 words</div>
                        </div>
                        <div style="text-align: center;">
                            <div id="audit-score-circle" style="width: 84px; height: 84px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; box-shadow: 0 4px 10px rgba(27,94,32,.3);">
                                85
                            </div>
                        </div>
                    </div>

                    <!-- Issues & Feedback -->
                    <div class="grid grid-2 mb-4" style="gap: 1.25rem;">
                        <!-- Issues Detected -->
                        <div style="background: #FFF5F5; border: 1px solid #FED7D7; border-radius: 12px; padding: 1.25rem;">
                            <h4 style="color: #C53030; font-size: 1.05rem; font-weight: 700; margin-bottom: .75rem;">⚠️ Issues Found</h4>
                            <div id="audit-issues-list" style="font-size: .9rem; display: flex; flex-direction: column; gap: .6rem;"></div>
                        </div>

                        <!-- Recommendations -->
                        <div style="background: #FFFAF0; border: 1px solid #FEEBC8; border-radius: 12px; padding: 1.25rem;">
                            <h4 style="color: #DD6B20; font-size: 1.05rem; font-weight: 700; margin-bottom: .75rem;">💡 Actionable Improvements</h4>
                            <div id="audit-suggestions-list" style="font-size: .9rem; display: flex; flex-direction: column; gap: .6rem;"></div>
                        </div>
                    </div>

                    <!-- Enhanced CV Output -->
                    <div style="background: #F0FFF4; border: 1px solid #C6F6D5; border-radius: 12px; padding: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: .75rem; flex-wrap: wrap; gap: .5rem;">
                            <h4 style="color: #22543D; font-size: 1.1rem; font-weight: 700; margin: 0;">✨ Auto-Modified &amp; Enhanced Version</h4>
                            <div style="display: flex; gap: .5rem;">
                                <button type="button" class="btn btn-outline btn-sm" onclick="copyCvText('audit-enhanced-text')">📋 Copy Enhanced</button>
                                <button type="button" class="btn btn-primary btn-sm" onclick="downloadCvAsFile('audit-enhanced-text', 'Enhanced_CV.txt')">💾 Download</button>
                            </div>
                        </div>
                        <p style="font-size: .85rem; color: #276749; margin-bottom: .75rem;">This version restructures headers, boosts active verbs, and cleans formatting for maximum ATS compatibility.</p>
                        <textarea id="audit-enhanced-text" class="form-control" rows="12" readonly style="font-family: monospace; font-size: .9rem; background: #fff;"></textarea>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

@push('scripts')
<script>
function switchCvTab(tab) {
    const btnGen = document.getElementById('tab-btn-generate');
    const btnAna = document.getElementById('tab-btn-analyze');
    const pnlGen = document.getElementById('cv-panel-generate');
    const pnlAna = document.getElementById('cv-panel-analyze');

    if (tab === 'generate') {
        btnGen.classList.add('btn-primary', 'active');
        btnGen.classList.remove('btn-outline');
        btnAna.classList.remove('btn-primary', 'active');
        btnAna.classList.add('btn-outline');
        pnlGen.classList.remove('hidden');
        pnlAna.classList.add('hidden');
    } else {
        btnAna.classList.add('btn-primary', 'active');
        btnAna.classList.remove('btn-outline');
        btnGen.classList.remove('btn-primary', 'active');
        btnGen.classList.add('btn-outline');
        pnlAna.classList.remove('hidden');
        pnlGen.classList.add('hidden');
    }
}

async function handleCvGenerate(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-generate');
    btn.disabled = true;
    btn.textContent = 'Generating CV...';

    const form = document.getElementById('cv-generate-form');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    try {
        const res = await api.post('/api/cv/generate', data);
        if (res.success) {
            document.getElementById('gen-output-text').value = res.cv_text;
            document.getElementById('gen-output-html').innerHTML = res.cv_html;
            document.getElementById('gen-result-container').classList.remove('hidden');
            showToast('CV generated successfully!', 'success');
            document.getElementById('gen-result-container').scrollIntoView({ behavior: 'smooth' });
        } else {
            showToast(res.message || 'Generation failed.', 'error');
        }
    } catch (err) {
        showToast(err.message || 'An error occurred while generating CV.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = '🚀 Generate Professional CV';
    }
}

async function handleCvAnalyze(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-analyze');
    btn.disabled = true;
    btn.textContent = 'Auditing CV...';

    const form = document.getElementById('cv-analyze-form');
    const formData = new FormData(form);

    try {
        const res = await fetch('/api/cv/analyze', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        const data = await res.json();

        if (data.success) {
            const a = data.analysis;
            document.getElementById('audit-score-circle').textContent = a.score;
            document.getElementById('audit-rating-title').textContent = a.rating;
            document.getElementById('audit-word-count').textContent = 'Word Count: ' + a.word_count + ' words';
            
            // Score circle color
            const circle = document.getElementById('audit-score-circle');
            if (a.score >= 80) circle.style.background = '#2E7D32';
            else if (a.score >= 60) circle.style.background = '#F59E0B';
            else circle.style.background = '#C62828';

            // Populate issues
            const issuesEl = document.getElementById('audit-issues-list');
            issuesEl.innerHTML = '';
            if (a.issues.length === 0) {
                issuesEl.innerHTML = '<div style="color: #2E7D32;">✓ No critical issues detected!</div>';
            } else {
                a.issues.forEach(iss => {
                    issuesEl.innerHTML += '<div><strong>• ' + escapeHtml(iss.title) + ':</strong> ' + escapeHtml(iss.description) + '</div>';
                });
            }

            // Populate suggestions
            const suggEl = document.getElementById('audit-suggestions-list');
            suggEl.innerHTML = '';
            if (a.suggestions.length === 0) {
                suggEl.innerHTML = '<div style="color: #2E7D32;">✓ Great job! Your CV adheres to standard practices.</div>';
            } else {
                a.suggestions.forEach(s => {
                    suggEl.innerHTML += '<div><strong>• ' + escapeHtml(s.title) + ':</strong> ' + escapeHtml(s.description) + '</div>';
                });
            }

            // Enhanced CV
            document.getElementById('audit-enhanced-text').value = data.modified_cv;

            document.getElementById('audit-results-container').classList.remove('hidden');
            showToast('Audit complete!', 'success');
            document.getElementById('audit-results-container').scrollIntoView({ behavior: 'smooth' });
        } else {
            showToast(data.message || 'Audit failed.', 'error');
        }
    } catch (err) {
        showToast(err.message || 'An error occurred during CV analysis.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = '🔬 Audit & Modify My CV Now';
    }
}

function copyCvText(elementId) {
    const el = document.getElementById(elementId);
    el.select();
    navigator.clipboard.writeText(el.value).then(() => {
        showToast('Copied to clipboard!', 'success');
    }).catch(() => {
        document.execCommand('copy');
        showToast('Copied to clipboard!', 'success');
    });
}

function downloadCvAsFile(elementId, filename) {
    const text = document.getElementById(elementId).value;
    const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}
</script>
@endpush
@endsection
