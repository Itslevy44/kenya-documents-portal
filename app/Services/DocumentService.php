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
        
        $html = '<div style="padding: 20px;">';
        
        // Add title
        if (isset($definition['title'])) {
            $html .= '<h1>' . htmlspecialchars($definition['title']) . '</h1>';
        }
        
        // Process content blocks
        if (isset($definition['content']) && is_array($definition['content'])) {
            foreach ($definition['content'] as $block) {
                $html .= $this->processContentBlock($block, $formData);
            }
        }
        
        // Add date
        $html .= '<div class="date">Date: ' . now()->format('d/m/Y') . '</div>';
        
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
                return '<h' . $level . '>' . htmlspecialchars($content) . '</h' . $level . '>';
                
            case 'paragraph':
                return '<p>' . nl2br(htmlspecialchars($content)) . '</p>';
                
            case 'list':
                $items = $block['items'] ?? [];
                $html = '<ul>';
                foreach ($items as $item) {
                    $html .= '<li>' . htmlspecialchars($this->replacePlaceholders($item, $formData)) . '</li>';
                }
                $html .= '</ul>';
                return $html;
                
            default:
                return '<p>' . htmlspecialchars($content) . '</p>';
        }
    }

    /**
     * Replace placeholders like {{field_name}} with actual form data
     */
    protected function replacePlaceholders(string $content, array $formData): string
    {
        return preg_replace_callback('/\{\{([a-zA-Z0-9_]+)\}\}/', function($matches) use ($formData) {
            $key = $matches[1];
            return $formData[$key] ?? '[' . $key . ']';
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
        
        // Process content blocks
        if (isset($definition['content']) && is_array($definition['content'])) {
            foreach ($definition['content'] as $block) {
                $this->processWordContentBlock($section, $block, $formData);
            }
        }
        
        // Add date
        $section->addTextBreak(1);
        $section->addText('Date: ' . now()->format('d/m/Y'), ['size' => 12]);
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
