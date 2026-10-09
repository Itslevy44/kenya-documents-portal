<?php

namespace App\Services;

use App\Models\GeneratedDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    /**
     * Generate preview PDF with watermark
     *
     * @param GeneratedDocument $doc
     * @return string Path to generated preview file
     */
    public function generatePreview(GeneratedDocument $doc): string
    {
        $html = $this->buildHtmlFromTemplate($doc);
        
        // Add watermark CSS
        $watermarkCss = '
            <style>
                @page { margin: 25mm; }
                body { 
                    font-family: "Times New Roman", Times, serif; 
                    font-size: 12pt; 
                    line-height: 1.5; 
                    color: #212121;
                    position: relative;
                }
                .watermark {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%) rotate(-45deg);
                    font-size: 72pt;
                    color: rgba(220, 20, 60, 0.2);
                    font-weight: bold;
                    text-align: center;
                    width: 100%;
                    z-index: -1;
                    pointer-events: none;
                }
            </style>
        ';
        
        $watermarkDiv = '<div class="watermark">PREVIEW<br>Not for official use</div>';
        $fullHtml = $watermarkCss . $watermarkDiv . $html;

        // Generate PDF
        $pdf = Pdf::loadHTML($fullHtml);
        $pdf->setPaper('a4', 'portrait');
        
        $fileName = $doc->session_token . '-preview.pdf';
        $path = 'documents/' . $fileName;
        
        Storage::put($path, $pdf->output());
        
        $doc->update(['preview_path' => $path]);
        
        return $path;
    }

    /**
     * Generate final documents (PDF + Word) without watermark
     *
     * @param GeneratedDocument $doc
     * @return array ['pdf' => path, 'word' => path]
     */
    public function generateDocuments(GeneratedDocument $doc): array
    {
        $template = $doc->template;
        $slug = $template->slug;
        $token = $doc->session_token;
        
        // Generate PDF
        $pdfPath = $this->generatePdf($doc, $slug, $token);
        
        // Generate Word
        $wordPath = $this->generateWord($doc, $slug, $token);
        
        $doc->update([
            'pdf_path' => $pdfPath,
            'word_path' => $wordPath,
        ]);
        
        return [
            'pdf' => $pdfPath,
            'word' => $wordPath,
        ];
    }

    /**
     * Generate clean PDF without watermark
     */
    protected function generatePdf(GeneratedDocument $doc, string $slug, string $token): string
    {
        $html = $this->buildHtmlFromTemplate($doc);
        
        $css = '
            <style>
                @page { margin: 25mm; }
                body { 
                    font-family: "Times New Roman", Times, serif; 
                    font-size: 12pt; 
                    line-height: 1.5; 
                    color: #212121;
                }
                h1 { text-align: center; font-size: 16pt; margin-bottom: 1em; }
                h2 { font-size: 14pt; margin-top: 1.5em; margin-bottom: 0.5em; }
                p { margin-bottom: 0.75em; text-align: justify; }
                .signature-block { 
                    margin-top: 3em; 
                    border-top: 1px solid #000; 
                    padding-top: 0.5em; 
                    width: 200px; 
                }
                .date { margin-top: 1.5em; }
                .footer { position: fixed; bottom: 10mm; right: 10mm; font-size: 10pt; color: #666; }
            </style>
        ';
        
        $footer = '<div class="footer">Page <script>document.write(pageNum);</script></div>';
        $fullHtml = $css . $html . $footer;

        $pdf = Pdf::loadHTML($fullHtml);
        $pdf->setPaper('a4', 'portrait');
        
        $fileName = $slug . '-' . $token . '.pdf';
        $path = 'documents/' . $fileName;
        
        Storage::put($path, $pdf->output());
        
        return $path;
    }

    /**
     * Generate Word document
     */
    protected function generateWord(GeneratedDocument $doc, string $slug, string $token): string
    {
        $phpWord = new PhpWord();
        
        // Set document properties
        $phpWord->getSettings()->setThemeFontLang(new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::EN_GB));
        
        $section = $phpWord->addSection([
            'marginLeft' => 1417,   // 25mm in twips
            'marginRight' => 1417,
            'marginTop' => 1417,
            'marginBottom' => 1417,
        ]);
        
        // Default font
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);
        
        // Parse template definition and build document
        $this->buildWordFromTemplate($section, $doc);
        
        // Add signature line
        $section->addTextBreak(2);
        $section->addText('_____________________________', ['size' => 12]);
        $section->addText('Signature', ['size' => 10, 'italic' => true]);
        
        $section->addTextBreak(1);
        $section->addText('Date: _______________', ['size' => 12]);
        
        // Save to storage
        $fileName = $slug . '-' . $token . '.docx';
        $path = 'documents/' . $fileName;
        $fullPath = storage_path('app/' . $path);
        
        // Ensure directory exists
        $dir = dirname($fullPath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($fullPath);
        
        return $path;
    }

    /**
     * Build HTML from template definition and form data
     */
    protected function buildHtmlFromTemplate(GeneratedDocument $doc): string
    {
        $template = $doc->template;
        $definition = $template->definition;
        $formData = $doc->form_data;
        
        $html = '<div style="padding: 20px; font-family: \'Times New Roman\', Times, serif; font-size: 12pt; line-height: 1.6; color: #111;">';
        
        // Add title
        if (isset($definition['title'])) {
            $html .= '<h1 style="text-align: center; font-size: 16pt; font-weight: bold; text-transform: uppercase; margin-bottom: 24px;">' . htmlspecialchars($definition['title']) . '</h1>';
        }
        
        // Process sections format (used in templates)
        if (isset($definition['sections']) && is_array($definition['sections'])) {
            foreach ($definition['sections'] as $sec) {
                // Style 1: heading + body_template
                if (isset($sec['body_template'])) {
                    if (!empty($sec['heading'])) {
                        $html .= '<h3 style="font-size: 13pt; font-weight: bold; margin-top: 18px; margin-bottom: 8px;">' . htmlspecialchars($this->replacePlaceholders($sec['heading'], $formData)) . '</h3>';
                    }
                    $bodyText = $this->replacePlaceholders($sec['body_template'], $formData);
                    $html .= '<div style="margin-bottom: 16px; white-space: pre-wrap;">' . nl2br(htmlspecialchars($bodyText)) . '</div>';
                    continue;
                }

                // Style 2: type + content
                $type = $sec['type'] ?? 'body';
                $content = $this->replacePlaceholders($sec['content'] ?? '', $formData);

                switch ($type) {
                    case 'header':
                        $html .= '<div style="text-align: left; margin-bottom: 20px; white-space: pre-wrap;">' . nl2br(htmlspecialchars($content)) . '</div>';
                        break;
                    case 'recipient':
                        $html .= '<div style="text-align: left; margin-bottom: 20px; font-weight: bold; white-space: pre-wrap;">' . nl2br(htmlspecialchars($content)) . '</div>';
                        break;
                    case 'subject':
                        $html .= '<div style="font-weight: bold; text-decoration: underline; margin-bottom: 16px; font-size: 12.5pt;">' . htmlspecialchars($content) . '</div>';
                        break;
                    case 'salutation':
                        $html .= '<div style="margin-bottom: 12px; font-weight: bold;">' . htmlspecialchars($content) . '</div>';
                        break;
                    case 'section_title':
                        $html .= '<h3 style="font-size: 13pt; font-weight: bold; margin-top: 20px; margin-bottom: 8px; border-bottom: 1px solid #ccc; padding-bottom: 4px;">' . htmlspecialchars($content) . '</h3>';
                        break;
                    case 'preamble':
                        $html .= '<div style="margin-bottom: 16px; text-align: justify; white-space: pre-wrap;">' . nl2br(htmlspecialchars($content)) . '</div>';
                        break;
                    case 'jurat':
                        $html .= '<div style="margin-top: 30px; padding: 14px; border: 1px solid #999; background: #fafafa; white-space: pre-wrap;">' . nl2br(htmlspecialchars($content)) . '</div>';
                        break;
                    case 'notice':
                        $html .= '<div style="margin-top: 20px; font-size: 10pt; font-style: italic; color: #555;">' . nl2br(htmlspecialchars($content)) . '</div>';
                        break;
                    case 'closing':
                        $html .= '<div style="margin-top: 24px; white-space: pre-wrap;">' . nl2br(htmlspecialchars($content)) . '</div>';
                        break;
                    default:
                        $html .= '<div style="margin-bottom: 14px; text-align: justify; white-space: pre-wrap;">' . nl2br(htmlspecialchars($content)) . '</div>';
                        break;
                }
            }
        }
        // Process content blocks format
        elseif (isset($definition['content']) && is_array($definition['content'])) {
            foreach ($definition['content'] as $block) {
                $html .= $this->processContentBlock($block, $formData);
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Process a single content block
     */
    protected function processContentBlock(array $block, array $formData): string
    {
        $type = $block['type'] ?? 'paragraph';
        $content = $block['content'] ?? '';
        
        // Replace placeholders with form data
        $content = $this->replacePlaceholders($content, $formData);
        
        switch ($type) {
            case 'heading':
                $level = $block['level'] ?? 2;
                return '<h' . $level . ' style="font-weight: bold; margin-top: 16px; margin-bottom: 8px;">' . htmlspecialchars($content) . '</h' . $level . '>';
                
            case 'paragraph':
                return '<p style="margin-bottom: 12px; text-align: justify;">' . nl2br(htmlspecialchars($content)) . '</p>';
                
            case 'list':
                $items = $block['items'] ?? [];
                $html = '<ul style="margin-bottom: 12px; padding-left: 20px;">';
                foreach ($items as $item) {
                    $html .= '<li>' . htmlspecialchars($this->replacePlaceholders($item, $formData)) . '</li>';
                }
                $html .= '</ul>';
                return $html;
                
            default:
                return '<p style="margin-bottom: 12px;">' . htmlspecialchars($content) . '</p>';
        }
    }

    /**
     * Replace placeholders like {{field_name}} with actual form data
     */
    protected function replacePlaceholders(string $content, array $formData): string
    {
        return preg_replace_callback('/\{\{([a-zA-Z0-9_]+)\}\}/', function($matches) use ($formData) {
            $key = $matches[1];
            return isset($formData[$key]) && $formData[$key] !== '' ? $formData[$key] : '[' . $key . ']';
        }, $content);
    }

    /**
     * Build Word document from template
     */
    protected function buildWordFromTemplate($section, GeneratedDocument $doc): void
    {
        $template = $doc->template;
        $definition = $template->definition;
        $formData = $doc->form_data;
        
        // Add title
        if (isset($definition['title'])) {
            $section->addText(
                $definition['title'],
                ['bold' => true, 'size' => 16],
                ['alignment' => Jc::CENTER]
            );
            $section->addTextBreak(1);
        }
        
        // Process sections format
        if (isset($definition['sections']) && is_array($definition['sections'])) {
            foreach ($definition['sections'] as $sec) {
                if (isset($sec['body_template'])) {
                    if (!empty($sec['heading'])) {
                        $section->addText(
                            $this->replacePlaceholders($sec['heading'], $formData),
                            ['bold' => true, 'size' => 13],
                            ['spaceBefore' => 180, 'spaceAfter' => 80]
                        );
                    }
                    $lines = explode("\n", $this->replacePlaceholders($sec['body_template'], $formData));
                    foreach ($lines as $line) {
                        $section->addText($line, ['size' => 12]);
                    }
                    $section->addTextBreak(1);
                    continue;
                }

                $type = $sec['type'] ?? 'body';
                $content = $this->replacePlaceholders($sec['content'] ?? '', $formData);
                $lines = explode("\n", $content);

                if ($type === 'subject') {
                    $section->addText($content, ['bold' => true, 'underline' => 'single', 'size' => 12.5], ['spaceBefore' => 120, 'spaceAfter' => 120]);
                } elseif ($type === 'section_title' || $type === 'heading') {
                    $section->addText($content, ['bold' => true, 'size' => 13], ['spaceBefore' => 180, 'spaceAfter' => 80]);
                } else {
                    foreach ($lines as $l) {
                        $section->addText($l, ['size' => 12]);
                    }
                    $section->addTextBreak(1);
                }
            }
        }
        // Process content blocks
        elseif (isset($definition['content']) && is_array($definition['content'])) {
            foreach ($definition['content'] as $block) {
                $this->processWordContentBlock($section, $block, $formData);
            }
        }

    /**
     * Process a content block for Word document
     */
    protected function processWordContentBlock($section, array $block, array $formData): void
    {
        $type = $block['type'] ?? 'paragraph';
        $content = $block['content'] ?? '';
        
        // Replace placeholders
        $content = $this->replacePlaceholders($content, $formData);
        
        switch ($type) {
            case 'heading':
                $level = $block['level'] ?? 2;
                $section->addText(
                    $content,
                    ['bold' => true, 'size' => 14],
                    ['spaceBefore' => 200, 'spaceAfter' => 100]
                );
                break;
                
            case 'paragraph':
                $section->addText(
                    $content,
                    ['size' => 12],
                    ['alignment' => Jc::BOTH, 'spaceAfter' => 120]
                );
                break;
                
            case 'list':
                $items = $block['items'] ?? [];
                foreach ($items as $item) {
                    $section->addListItem(
                        $this->replacePlaceholders($item, $formData),
                        0,
                        ['size' => 12]
                    );
                }
                break;
                
            default:
                $section->addText($content, ['size' => 12]);
                break;
        }
    }
}
