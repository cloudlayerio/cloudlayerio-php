<?php

declare(strict_types=1);

namespace CloudLayer\Types;

/**
 * Job type string constants.
 *
 * Not an enum because the set may grow and values are user-facing strings.
 */
final class JobType
{
    public const URL_IMAGE = 'url-image';
    public const URL_PDF = 'url-pdf';
    public const HTML_IMAGE = 'html-image';
    public const HTML_PDF = 'html-pdf';
    public const TEMPLATE_PDF = 'template-pdf';
    public const TEMPLATE_IMAGE = 'template-image';
    public const DOCX_PDF = 'docx-pdf';
    public const DOCX_HTML = 'docx-html';
    public const IMAGE_PDF = 'image-pdf';
    public const PDF_IMAGE = 'pdf-image';
    public const PDF_DOCX = 'pdf-docx';
    public const MERGE_PDF = 'merge-pdf';

    private function __construct()
    {
    }
}
