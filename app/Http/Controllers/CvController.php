<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CvController extends Controller
{
    /**
     * Display the CV Assistant workspace
     */
    public function index()
    {
        return view('cv-assistant');
    }

    /**
     * Generate a new professional CV from structured form data
     */
    public function generateApi(Request $request)
    {
        $validated = $request->validate([
            'full_name'      => 'required|string|max:120',
            'job_title'      => 'required|string|max:120',
            'phone'          => 'required|string|max:30',
            'email'          => 'required|email|max:120',
            'location'       => 'nullable|string|max:120',
            'linkedin'       => 'nullable|string|max:160',
            'summary'        => 'required|string|max:1500',
            'experience'     => 'nullable|string|max:4000',
            'education'      => 'required|string|max:3000',
            'skills'         => 'required|string|max:1500',
            'certifications' => 'nullable|string|max:1500',
            'languages'      => 'nullable|string|max:500',
            'references'     => 'nullable|string|max:1000',
        ]);

        $formatted = $this->buildFormattedCv($validated);

        return response()->json([
            'success' => true,
            'cv_text' => $formatted['plain'],
            'cv_html' => $formatted['html'],
            'message' => 'Professional CV successfully generated!',
        ]);
    }

    /**
     * Audit an existing CV (text-only input — no binary PDF parsing)
     */
    public function analyzeApi(Request $request)
    {
        $request->validate([
            'cv_file' => 'nullable|file|mimes:txt|max:2048',
            'cv_text' => 'nullable|string|max:20000',
        ]);

        $cvText = '';

        // Accept plain-text file uploads only
        if ($request->hasFile('cv_file')) {
            $file      = $request->file('cv_file');
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'txt') {
                $cvText = file_get_contents($file->getRealPath());
                // Sanitise: strip non-printable control characters but keep newlines/tabs
                $cvText = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cvText);
            } else {
                // Binary formats (PDF/DOCX) cannot be read without a native binary
                return response()->json([
                    'success' => false,
                    'message' => 'PDF and Word file parsing is not supported on this server. Please copy your CV text and paste it into the text area below for an accurate audit.',
                ], 422);
            }
        }

        // Fall back to pasted text
        if (empty(trim($cvText))) {
            $cvText = (string) $request->input('cv_text', '');
        }

        $cvText = trim($cvText);

        if (strlen($cvText) < 80) {
            return response()->json([
                'success' => false,
                'message' => 'Your CV text is too short (minimum 80 characters). Please paste the full text of your CV.',
            ], 422);
        }

        $analysis    = $this->performCvAudit($cvText);
        $modifiedCv  = $this->generateEnhancedVersion($cvText, $analysis);

        return response()->json([
            'success'     => true,
            'analysis'    => $analysis,
            'modified_cv' => $modifiedCv,
            'message'     => 'CV audit complete!',
        ]);
    }

    // =========================================================================
    // CV Builder
    // =========================================================================

    /**
     * Build a clean, ATS-friendly, professionally formatted CV
     */
    protected function buildFormattedCv(array $d): array
    {
        $name     = strtoupper(trim($d['full_name']));
        $title    = trim($d['job_title']);
        $phone    = trim($d['phone']);
        $email    = trim($d['email']);
        $loc      = !empty($d['location']) ? trim($d['location']) : 'Nairobi, Kenya';
        $linkedin = !empty($d['linkedin']) ? trim($d['linkedin']) : '';

        $skillsList = array_filter(array_map('trim', explode(',', $d['skills'])));

        // ── Plain Text ──────────────────────────────────────────────────────
        $p  = "=================================================================\n";
        $p .= "{$name}\n";
        $p .= "{$title}\n";
        $p .= "Phone: {$phone}  |  Email: {$email}  |  Location: {$loc}\n";
        if ($linkedin) {
            $p .= "LinkedIn: {$linkedin}\n";
        }
        $p .= "=================================================================\n\n";

        $p .= "PROFESSIONAL SUMMARY\n";
        $p .= "--------------------\n";
        $p .= wordwrap(trim($d['summary']), 90, "\n", false) . "\n\n";

        if (!empty($d['experience'])) {
            $p .= "WORK EXPERIENCE\n";
            $p .= "---------------\n";
            $p .= trim($d['experience']) . "\n\n";
        }

        $p .= "EDUCATION\n";
        $p .= "---------\n";
        $p .= trim($d['education']) . "\n\n";

        $p .= "CORE SKILLS & COMPETENCIES\n";
        $p .= "---------------------------\n";
        foreach ($skillsList as $s) {
            $p .= "  • " . $s . "\n";
        }
        $p .= "\n";

        if (!empty($d['certifications'])) {
            $p .= "CERTIFICATIONS & TRAINING\n";
            $p .= "-------------------------\n";
            $p .= trim($d['certifications']) . "\n\n";
        }

        if (!empty($d['languages'])) {
            $p .= "LANGUAGES\n";
            $p .= "---------\n";
            $p .= trim($d['languages']) . "\n\n";
        }

        $p .= "REFEREES\n";
        $p .= "--------\n";
        $p .= !empty($d['references']) ? trim($d['references']) . "\n" : "Available upon request.\n";

        // ── HTML ────────────────────────────────────────────────────────────
        $h  = '<div class="cv-preview-sheet">';

        // Header
        $h .= '<header class="cv-header">';
        $h .= '<h1 class="cv-name">' . htmlspecialchars($name) . '</h1>';
        $h .= '<div class="cv-title-line">' . htmlspecialchars($title) . '</div>';
        $h .= '<div class="cv-contact-bar">';
        $h .= '<span><svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg> ' . htmlspecialchars($phone) . '</span>';
        $h .= '<span><svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg> ' . htmlspecialchars($email) . '</span>';
        $h .= '<span><svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg> ' . htmlspecialchars($loc) . '</span>';
        if ($linkedin) {
            $h .= '<span><svg viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg> ' . htmlspecialchars($linkedin) . '</span>';
        }
        $h .= '</div>';
        $h .= '</header>';

        // Summary
        $h .= '<section class="cv-section">';
        $h .= '<h2 class="cv-heading"><span>Professional Summary</span></h2>';
        $h .= '<p>' . nl2br(htmlspecialchars(trim($d['summary']))) . '</p>';
        $h .= '</section>';

        // Experience
        if (!empty($d['experience'])) {
            $h .= '<section class="cv-section">';
            $h .= '<h2 class="cv-heading"><span>Work Experience</span></h2>';
            $h .= '<div class="cv-block">' . nl2br(htmlspecialchars(trim($d['experience']))) . '</div>';
            $h .= '</section>';
        }

        // Education
        $h .= '<section class="cv-section">';
        $h .= '<h2 class="cv-heading"><span>Education</span></h2>';
        $h .= '<div class="cv-block">' . nl2br(htmlspecialchars(trim($d['education']))) . '</div>';
        $h .= '</section>';

        // Skills
        $h .= '<section class="cv-section">';
        $h .= '<h2 class="cv-heading"><span>Core Skills & Competencies</span></h2>';
        $h .= '<ul class="cv-skills-grid">';
        foreach ($skillsList as $s) {
            $h .= '<li>' . htmlspecialchars($s) . '</li>';
        }
        $h .= '</ul>';
        $h .= '</section>';

        // Certifications
        if (!empty($d['certifications'])) {
            $h .= '<section class="cv-section">';
            $h .= '<h2 class="cv-heading"><span>Certifications & Training</span></h2>';
            $h .= '<div class="cv-block">' . nl2br(htmlspecialchars(trim($d['certifications']))) . '</div>';
            $h .= '</section>';
        }

        // Languages
        if (!empty($d['languages'])) {
            $h .= '<section class="cv-section">';
            $h .= '<h2 class="cv-heading"><span>Languages</span></h2>';
            $h .= '<p>' . htmlspecialchars(trim($d['languages'])) . '</p>';
            $h .= '</section>';
        }

        // Referees
        $h .= '<section class="cv-section">';
        $h .= '<h2 class="cv-heading"><span>Referees</span></h2>';
        $refText = !empty($d['references']) ? trim($d['references']) : 'Available upon request.';
        $h .= '<p>' . nl2br(htmlspecialchars($refText)) . '</p>';
        $h .= '</section>';

        $h .= '</div>';

        return ['plain' => $p, 'html' => $h];
    }

    // =========================================================================
    // CV Audit
    // =========================================================================

    /**
     * Score and analyse clean plain-text CV content
     */
    protected function performCvAudit(string $text): array
    {
        $score       = 100;
        $issues      = [];
        $suggestions = [];
        $positives   = [];

        // Normalise whitespace for reliable matching
        $normalised = preg_replace('/[ \t]+/', ' ', $text);
        $words      = str_word_count($normalised);

        // ── 1. Word count ─────────────────────────────────────────────────
        if ($words < 180) {
            $score -= 20;
            $issues[] = [
                'type'        => 'critical',
                'title'       => 'CV is too brief',
                'description' => "Your CV has only {$words} words. Kenyan employers expect a comprehensive profile of 350–700 words.",
            ];
        } elseif ($words > 1000) {
            $score -= 5;
            $suggestions[] = [
                'type'        => 'info',
                'title'       => 'CV may exceed two pages',
                'description' => "At {$words} words your CV risks losing recruiter attention. Trim older roles to stay within 700 words.",
            ];
        } else {
            $positives[] = "Good CV length ({$words} words).";
        }

        // ── 2. Kenyan phone number ────────────────────────────────────────
        // Matches 07XXXXXXXX, 01XXXXXXXX, +2547XXXXXXXX, 2547XXXXXXXX
        if (!preg_match('/(\b0[17]\d{8}\b|\+254[17]\d{8}\b|254[17]\d{8}\b)/', $normalised)) {
            $score -= 15;
            $issues[] = [
                'type'        => 'critical',
                'title'       => 'Missing Kenyan phone number',
                'description' => 'Add a phone number in the format 07XXXXXXXX or +254XXXXXXXX so recruiters can reach you immediately.',
            ];
        } else {
            $positives[] = 'Valid Kenyan phone number present.';
        }

        // ── 3. Email address ──────────────────────────────────────────────
        if (!preg_match('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $normalised)) {
            $score -= 15;
            $issues[] = [
                'type'        => 'critical',
                'title'       => 'No email address found',
                'description' => 'Every CV must include a professional email address. Avoid casual handles; use firstname.lastname@domain.com.',
            ];
        } else {
            $positives[] = 'Email address present.';
        }

        // ── 4. Key sections ───────────────────────────────────────────────
        $sectionChecks = [
            'Professional Summary / Objective' => [
                'summary', 'objective', 'profile', 'about me', 'career profile',
                'professional profile', 'executive summary',
            ],
            'Work Experience' => [
                'experience', 'work history', 'employment history', 'work experience',
                'career history', 'professional experience', 'responsibilities', 'duties',
                'position', 'role', 'worked at', 'working at',
            ],
            'Education' => [
                'education', 'academic', 'kcse', 'kcpe', 'degree', 'diploma',
                'certificate', 'university', 'college', 'school', 'institute',
                'bachelor', 'master', 'phd', 'mba',
            ],
            'Skills' => [
                'skills', 'competencies', 'expertise', 'proficiencies',
                'core competencies', 'key skills', 'technical skills', 'tools',
                'abilities',
            ],
            'Referees / References' => [
                'referee', 'reference', 'available upon request',
                'references available', 'provided upon request',
            ],
        ];

        foreach ($sectionChecks as $sectionName => $keywords) {
            $found = false;
            foreach ($keywords as $kw) {
                if (stripos($normalised, $kw) !== false) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $score -= 8;
                $issues[] = [
                    'type'        => 'warning',
                    'title'       => "Missing '{$sectionName}' section",
                    'description' => "Add a clearly labelled {$sectionName} section. Kenyan hiring managers scan for these headings before reading the detail.",
                ];
            } else {
                $positives[] = "Contains {$sectionName} section.";
            }
        }

        // ── 5. Action verbs ───────────────────────────────────────────────
        $actionVerbs = [
            'managed', 'coordinated', 'developed', 'spearheaded', 'implemented',
            'achieved', 'streamlined', 'designed', 'built', 'led', 'negotiated',
            'improved', 'increased', 'trained', 'audited', 'supervised', 'delivered',
            'launched', 'reduced', 'generated', 'established', 'maintained',
            'analysed', 'analyzed', 'reported', 'administered',
        ];

        $foundVerbs = 0;
        foreach ($actionVerbs as $verb) {
            if (preg_match('/\b' . preg_quote($verb, '/') . '\b/i', $normalised)) {
                $foundVerbs++;
            }
        }

        if ($foundVerbs < 3) {
            $score -= 10;
            $suggestions[] = [
                'type'        => 'action',
                'title'       => 'Use stronger action verbs',
                'description' => 'Replace passive phrases with active verbs: "Managed", "Spearheaded", "Implemented", "Increased", "Streamlined". Found ' . $foundVerbs . ' — aim for 5+.',
            ];
        } else {
            $positives[] = "Uses {$foundVerbs} strong action verbs.";
        }

        // ── 6. Quantified achievements ────────────────────────────────────
        // Numbers, percentages, Kenyan currency, counts
        if (!preg_match('/(\d+\s*%|\d+\s*\+|KSh\s*[\d,]+|KES\s*[\d,]+|\b\d{2,}[\d,]*\b)/', $normalised)) {
            $score -= 8;
            $suggestions[] = [
                'type'        => 'action',
                'title'       => 'Quantify your achievements',
                'description' => 'Numbers make your impact concrete: "Increased sales by 30%", "Managed a budget of KSh 500,000", "Trained 20+ staff members".',
            ];
        } else {
            $positives[] = 'Contains measurable, quantified achievements.';
        }

        // ── 7. Contact section completeness ───────────────────────────────
        $hasLocation = preg_match('/\b(nairobi|mombasa|kisumu|nakuru|eldoret|thika|kenya|county|town|city)\b/i', $normalised);
        if (!$hasLocation) {
            $score -= 5;
            $suggestions[] = [
                'type'        => 'info',
                'title'       => 'Add your location',
                'description' => 'Include your city or county (e.g. "Nairobi, Kenya") so employers can confirm you are locally available.',
            ];
        } else {
            $positives[] = 'Location/city mentioned.';
        }

        // ── 8. Length of professional summary ─────────────────────────────
        // Try to extract summary block and check it is substantial
        if (preg_match('/(?:summary|objective|profile)[:\s\n]+(.{20,400})/is', $normalised, $sm)) {
            $summaryWords = str_word_count($sm[1]);
            if ($summaryWords < 30) {
                $suggestions[] = [
                    'type'        => 'action',
                    'title'       => 'Expand your professional summary',
                    'description' => 'Your summary appears brief (' . $summaryWords . ' words). A strong summary is 40–80 words and captures your experience, expertise, and value proposition.',
                ];
                $score -= 5;
            } else {
                $positives[] = 'Professional summary has good depth.';
            }
        }

        $score = max(10, min(100, $score));

        if ($score >= 85) {
            $rating     = 'Outstanding';
            $badgeClass = 'badge-success';
            $color      = '#16a34a';
        } elseif ($score >= 70) {
            $rating     = 'Strong';
            $badgeClass = 'badge-primary';
            $color      = '#2563eb';
        } elseif ($score >= 50) {
            $rating     = 'Moderate';
            $badgeClass = 'badge-warning';
            $color      = '#d97706';
        } else {
            $rating     = 'Needs Improvement';
            $badgeClass = 'badge-danger';
            $color      = '#dc2626';
        }

        return [
            'score'       => $score,
            'rating'      => $rating,
            'badge_class' => $badgeClass,
            'color'       => $color,
            'word_count'  => $words,
            'issues'      => $issues,
            'suggestions' => $suggestions,
            'positives'   => $positives,
        ];
    }

    // =========================================================================
    // CV Enhancer
    // =========================================================================

    /**
     * Generate a restructured, ATS-ready version of the submitted plain-text CV
     */
    protected function generateEnhancedVersion(string $cvText, array $analysis): string
    {
        $lines    = preg_split('/\r\n|\r|\n/', trim($cvText));
        $enhanced = [];

        $enhanced[] = "=================================================================";
        $enhanced[] = "OPTIMISED CV — ATS-READY FORMAT";
        $enhanced[] = "Restructured for Kenyan recruiter & ATS compatibility";
        $enhanced[] = "=================================================================";
        $enhanced[] = "";

        $sectionHeaders = [
            '/^(personal\s+summary|professional\s+summary|executive\s+summary|career\s+objective|objective|profile|about\s+me)[:\s]*$/i' => 'PROFESSIONAL SUMMARY',
            '/^(work\s+experience|experience|employment\s+history|career\s+history|professional\s+experience)[:\s]*$/i'                  => 'WORK EXPERIENCE',
            '/^(education|academic\s+background|academic\s+qualifications|qualifications)[:\s]*$/i'                                     => 'EDUCATION',
            '/^(skills|core\s+skills|key\s+skills|technical\s+skills|core\s+competencies|competencies)[:\s]*$/i'                       => 'CORE SKILLS & COMPETENCIES',
            '/^(certifications?|training|professional\s+development|courses?)[:\s]*$/i'                                                  => 'CERTIFICATIONS & TRAINING',
            '/^(languages?|language\s+proficiency)[:\s]*$/i'                                                                            => 'LANGUAGES',
            '/^(referees?|references?)[:\s]*$/i'                                                                                        => 'REFEREES',
            '/^(achievements?|accomplishments?|awards?)[:\s]*$/i'                                                                       => 'KEY ACHIEVEMENTS',
            '/^(hobbies|interests|extra.?curricular)[:\s]*$/i'                                                                          => 'INTERESTS',
        ];

        // Passive → active verb replacements
        $passiveReplacements = [
            '/\bresponsible for\b/i'      => 'Managed',
            '/\bduties included?\b/i'     => 'Executed key duties:',
            '/\btasks?\s+were\b/i'        => 'Delivered tasks including:',
            '/\bhelped\s+to\b/i'          => 'Contributed to',
            '/\bwas\s+involved\s+in\b/i'  => 'Actively participated in',
            '/\bworked\s+on\b/i'          => 'Developed and maintained',
            '/\bassisted\s+in\b/i'        => 'Supported and contributed to',
            '/\bresponsible\s+for\b/i'    => 'Oversaw',
        ];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                $enhanced[] = '';
                continue;
            }

            // Check for section header match
            $matchedHeader = null;
            foreach ($sectionHeaders as $pattern => $replacement) {
                if (preg_match($pattern, $trimmed)) {
                    $matchedHeader = $replacement;
                    break;
                }
            }

            if ($matchedHeader !== null) {
                $enhanced[] = '';
                $enhanced[] = $matchedHeader;
                $enhanced[] = str_repeat('─', min(50, strlen($matchedHeader) + 4));
                continue;
            }

            // Apply passive → active verb upgrades
            $upgraded = $trimmed;
            foreach ($passiveReplacements as $pattern => $replacement) {
                $upgraded = preg_replace($pattern, $replacement, $upgraded);
            }

            // Ensure bullet points are consistent
            if (preg_match('/^[-*•◦]\s+/', $upgraded)) {
                $upgraded = '• ' . preg_replace('/^[-*•◦]\s+/', '', $upgraded);
            }

            $enhanced[] = $upgraded;
        }

        // Append improvement notes at the bottom
        $enhanced[] = '';
        $enhanced[] = '─────────────────────────────────────────────────────────────';
        $enhanced[] = 'OPTIMISATION NOTES (remove before submitting):';
        $enhanced[] = '─────────────────────────────────────────────────────────────';

        if (!empty($analysis['issues'])) {
            foreach ($analysis['issues'] as $issue) {
                $enhanced[] = '⚠  ' . $issue['title'] . ': ' . $issue['description'];
            }
        }
        if (!empty($analysis['suggestions'])) {
            foreach ($analysis['suggestions'] as $sug) {
                $enhanced[] = '💡 ' . $sug['title'] . ': ' . $sug['description'];
            }
        }

        return implode("\n", $enhanced);
    }
}
