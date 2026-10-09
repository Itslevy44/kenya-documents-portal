<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Generate a new professional CV
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
            'success'   => true,
            'cv_text'   => $formatted['plain'],
            'cv_html'   => $formatted['html'],
            'message'   => 'Professional CV successfully generated!',
        ]);
    }

    /**
     * Analyze and modify/enhance an existing CV
     */
    public function analyzeApi(Request $request)
    {
        $cvText = '';

        if ($request->hasFile('cv_file')) {
            $file = $request->file('cv_file');
            $extension = strtolower($file->getClientOriginalExtension());

            if (in_array($extension, ['txt', 'rtf', 'csv'])) {
                $cvText = file_get_contents($file->getRealPath());
            } elseif ($extension === 'pdf' || $extension === 'docx' || $extension === 'doc') {
                // Read text stream or binary preview fallback
                $content = @file_get_contents($file->getRealPath());
                // Strip binary control characters to extract raw ascii/utf-8 text
                $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', ' ', $content);
                // Extract words
                preg_match_all('/[a-zA-Z0-9\.\,\:\-\@\/\+\s]{3,}/', $clean, $matches);
                $cvText = implode(' ', $matches[0] ?? []);
                if (strlen(trim($cvText)) < 80) {
                    $cvText = $request->input('cv_text', '');
                }
            }
        }

        if (empty($cvText)) {
            $cvText = (string) $request->input('cv_text', '');
        }

        if (strlen(trim($cvText)) < 60) {
            return response()->json([
                'success' => false,
                'message' => 'Please paste your CV text or upload a readable file with at least 60 characters.',
            ], 422);
        }

        $analysis = $this->performCvAudit($cvText);
        $modifiedCv = $this->generateEnhancedVersion($cvText, $analysis);

        return response()->json([
            'success'     => true,
            'analysis'    => $analysis,
            'modified_cv' => $modifiedCv,
            'message'     => 'CV analysis and suggested modifications ready!',
        ]);
    }

    /**
     * Build clean ATS-friendly formatted CV
     */
    protected function buildFormattedCv(array $d): array
    {
        $name = strtoupper(trim($d['full_name']));
        $title = trim($d['job_title']);
        $phone = trim($d['phone']);
        $email = trim($d['email']);
        $loc = !empty($d['location']) ? trim($d['location']) : 'Nairobi, Kenya';
        $linkedin = !empty($d['linkedin']) ? trim($d['linkedin']) : '';

        // Plain Text Output
        $p = "=================================================================\n";
        $p .= "{$name}\n";
        $p .= "{$title}\n";
        $p .= "Phone: {$phone} | Email: {$email} | Location: {$loc}\n";
        if ($linkedin) $p .= "LinkedIn: {$linkedin}\n";
        $p .= "=================================================================\n\n";

        $p .= "PROFESSIONAL SUMMARY\n";
        $p .= "--------------------\n";
        $p .= trim($d['summary']) . "\n\n";

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
        $skillsList = array_filter(array_map('trim', explode(',', $d['skills'])));
        foreach ($skillsList as $s) {
            $p .= "• " . $s . "\n";
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
        $p .= !empty($d['references']) ? trim($d['references']) : "Available upon request.\n";

        // HTML Output
        $h = '<div class="cv-preview-sheet">';
        $h .= '<header class="cv-header">';
        $h .= '<h1 class="cv-name">' . htmlspecialchars($name) . '</h1>';
        $h .= '<div class="cv-title">' . htmlspecialchars($title) . '</div>';
        $h .= '<div class="cv-contact-bar">';
        $h .= '<span>📞 ' . htmlspecialchars($phone) . '</span>';
        $h .= '<span>✉ ' . htmlspecialchars($email) . '</span>';
        $h .= '<span>📍 ' . htmlspecialchars($loc) . '</span>';
        if ($linkedin) $h .= '<span>🔗 ' . htmlspecialchars($linkedin) . '</span>';
        $h .= '</div>';
        $h .= '</header>';

        $h .= '<section class="cv-section"><h2 class="cv-heading">Professional Summary</h2>';
        $h .= '<p>' . nl2br(htmlspecialchars(trim($d['summary']))) . '</p></section>';

        if (!empty($d['experience'])) {
            $h .= '<section class="cv-section"><h2 class="cv-heading">Work Experience</h2>';
            $h .= '<div>' . nl2br(htmlspecialchars(trim($d['experience']))) . '</div></section>';
        }

        $h .= '<section class="cv-section"><h2 class="cv-heading">Education</h2>';
        $h .= '<div>' . nl2br(htmlspecialchars(trim($d['education']))) . '</div></section>';

        $h .= '<section class="cv-section"><h2 class="cv-heading">Core Skills</h2><ul class="cv-skills-grid">';
        foreach ($skillsList as $s) {
            $h .= '<li>' . htmlspecialchars($s) . '</li>';
        }
        $h .= '</ul></section>';

        if (!empty($d['certifications'])) {
            $h .= '<section class="cv-section"><h2 class="cv-heading">Certifications</h2>';
            $h .= '<div>' . nl2br(htmlspecialchars(trim($d['certifications']))) . '</div></section>';
        }

        if (!empty($d['languages'])) {
            $h .= '<section class="cv-section"><h2 class="cv-heading">Languages</h2>';
            $h .= '<p>' . htmlspecialchars(trim($d['languages'])) . '</p></section>';
        }

        $h .= '<section class="cv-section"><h2 class="cv-heading">Referees</h2>';
        $h .= '<p>' . nl2br(htmlspecialchars(!empty($d['references']) ? trim($d['references']) : 'Available upon request.')) . '</p></section>';
        $h .= '</div>';

        return ['plain' => $p, 'html' => $h];
    }

    /**
     * Perform comprehensive CV audit
     */
    protected function performCvAudit(string $text): array
    {
        $score = 100;
        $issues = [];
        $suggestions = [];
        $positives = [];

        $words = str_word_count($text);

        // Word count check
        if ($words < 180) {
            $score -= 20;
            $issues[] = [
                'type' => 'critical',
                'title' => 'CV is too brief',
                'description' => "Your CV has only {$words} words. Kenyan employers expect a comprehensive profile between 350 - 700 words.",
            ];
        } elseif ($words > 900) {
            $score -= 5;
            $suggestions[] = [
                'type' => 'info',
                'title' => 'CV length exceeds 2 pages',
                'description' => "Your CV has {$words} words. Consider condensing older positions to maintain recruiter focus.",
            ];
        } else {
            $positives[] = "Optimal CV word count ({$words} words).";
        }

        // Phone number
        if (!preg_match('/(07\d{8}|01\d{8}|\+254\d{9})/', $text)) {
            $score -= 15;
            $issues[] = [
                'type' => 'critical',
                'title' => 'Missing Kenyan Phone Number',
                'description' => 'No standard Kenyan mobile number detected (format: 07XXXXXXXX or +254XXXXXXXX).',
            ];
        } else {
            $positives[] = 'Valid phone contact found.';
        }

        // Email address
        if (!preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text)) {
            $score -= 15;
            $issues[] = [
                'type' => 'critical',
                'title' => 'Missing Professional Email',
                'description' => 'Recruiters cannot contact you directly without an email address.',
            ];
        } else {
            $positives[] = 'Professional email address present.';
        }

        // Key sections check
        $sections = [
            'Education' => ['education', 'academic', 'kcse', 'degree', 'diploma', 'university', 'college', 'school'],
            'Work Experience' => ['experience', 'work history', 'employment', 'responsibilities', 'duties', 'role'],
            'Skills' => ['skills', 'competencies', 'expertise', 'proficiencies', 'tools'],
            'Profile Summary' => ['summary', 'objective', 'profile', 'about me'],
            'Referees' => ['referee', 'reference', 'available upon request'],
        ];

        foreach ($sections as $sectionName => $keywords) {
            $found = false;
            foreach ($keywords as $kw) {
                if (stripos($text, $kw) !== false) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $score -= 10;
                $issues[] = [
                    'type' => 'warning',
                    'title' => "Missing '{$sectionName}' Section",
                    'description' => "Kenyan hiring managers look for a dedicated {$sectionName} block to evaluate your qualifications.",
                ];
            } else {
                $positives[] = "Contains {$sectionName} section.";
            }
        }

        // Action verbs check
        $actionVerbs = ['managed', 'coordinated', 'developed', 'spearheaded', 'implemented', 'achieved', 'streamlined', 'designed', 'built', 'led', 'negotiated', 'improved', 'increased', 'trained', 'audited'];
        $foundVerbs = 0;
        foreach ($actionVerbs as $verb) {
            if (stripos($text, $verb) !== false) {
                $foundVerbs++;
            }
        }

        if ($foundVerbs < 3) {
            $score -= 10;
            $suggestions[] = [
                'type' => 'action',
                'title' => 'Use High-Impact Action Verbs',
                'description' => 'Replace passive phrases with impactful verbs such as "Spearheaded", "Implemented", "Managed", "Increased", or "Streamlined".',
            ];
        } else {
            $positives[] = "Uses strong action verbs ({$foundVerbs} found).";
        }

        // Quantification & numbers
        if (!preg_match('/(\d+%|\d+\+|KSh|KES|\b\d{2,}\b)/i', $text)) {
            $score -= 10;
            $suggestions[] = [
                'type' => 'action',
                'title' => 'Quantify Your Achievements',
                'description' => 'Add measurable outcomes (e.g. "Increased team efficiency by 25%", "Managed a budget of KSh 200,000", "Handled 50+ clients weekly").',
            ];
        } else {
            $positives[] = 'Contains measurable, quantified metrics.';
        }

        $score = max(20, min(100, $score));

        $rating = 'Needs Optimization';
        $badgeClass = 'badge-danger';
        if ($score >= 85) {
            $rating = 'Outstanding';
            $badgeClass = 'badge-success';
        } elseif ($score >= 70) {
            $rating = 'Strong';
            $badgeClass = 'badge-primary';
        } elseif ($score >= 50) {
            $rating = 'Moderate';
            $badgeClass = 'badge-warning';
        }

        return [
            'score'       => $score,
            'rating'      => $rating,
            'badge_class' => $badgeClass,
            'word_count'  => $words,
            'issues'      => $issues,
            'suggestions' => $suggestions,
            'positives'   => $positives,
        ];
    }

    /**
     * Generate enhanced/modified version of CV addressing weaknesses
     */
    protected function generateEnhancedVersion(string $cvText, array $analysis): string
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($cvText));
        $enhanced = [];

        $enhanced[] = "=================================================================";
        $enhanced[] = "RECOMMENDED ENHANCED & OPTIMIZED CV VERSION";
        $enhanced[] = "Refactored for ATS compatibility & Kenyan recruiter standards";
        $enhanced[] = "=================================================================\n";

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                $enhanced[] = "";
                continue;
            }

            // Capitalize section headers if found
            if (preg_match('/^(education|experience|work experience|skills|summary|profile|referees|certifications)[:\s]*$/i', $trimmed, $m)) {
                $enhanced[] = "\n" . strtoupper($m[1]);
                $enhanced[] = str_repeat('-', strlen($m[1]) + 8);
                continue;
            }

            // Upgrade bullet points with action verbs if starting with "Responsible for" or "Duties"
            $upgraded = preg_replace('/^(responsible for|duties included|tasks were)\s+/i', 'Successfully managed and spearheaded ', $trimmed);
            $enhanced[] = $upgraded;
        }

        return implode("\n", $enhanced);
    }
}
