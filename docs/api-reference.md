# API Reference

## CloudLayer Client

### Constructor

```php
new CloudLayer(
    string $apiKey,
    ApiVersion|string $apiVersion,
    string $baseUrl = 'https://api.cloudlayer.io',
    int $timeout = 30000,
    int $maxRetries = 2,
    array $headers = [],
)
```

**Parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$apiKey` | `string` | — | Your cloudlayer.io API key (required) |
| `$apiVersion` | `ApiVersion\|string` | — | `'v1'` or `'v2'` (required) |
| `$baseUrl` | `string` | `https://api.cloudlayer.io` | API base URL |
| `$timeout` | `int` | `30000` | Request timeout in milliseconds |
| `$maxRetries` | `int` | `2` | Max retries for retryable requests (0-5) |
| `$headers` | `array<string, string>` | `[]` | Additional HTTP headers |

**Throws:** `ConfigException` if any parameter is invalid.

### Conversion Methods

All conversion methods accept an `array<string, mixed>` of options and return `ConversionResult`.

| Method | Endpoint | Validation |
|--------|----------|------------|
| `urlToPdf(array $options)` | `POST /url/pdf` | URL + base options |
| `urlToImage(array $options)` | `POST /url/image` | URL + image + base options |
| `htmlToPdf(array $options)` | `POST /html/pdf` | HTML + base options |
| `htmlToImage(array $options)` | `POST /html/image` | HTML + image + base options |
| `templateToPdf(array $options)` | `POST /template/pdf` | Template + base options |
| `templateToImage(array $options)` | `POST /template/image` | Template + image + base options |
| `docxToPdf(array $options)` | `POST /docx/pdf` | File + base options (multipart) |
| `docxToHtml(array $options)` | `POST /docx/html` | File + base options (multipart) |
| `pdfToDocx(array $options)` | `POST /pdf/docx` | File + base options (multipart) |
| `mergePdfs(array $options)` | `POST /pdf/merge` | URL + base options |

### Data Management Methods

| Method | Endpoint | Returns | Retryable |
|--------|----------|---------|-----------|
| `listJobs()` | `GET /jobs` | `Job[]` | Yes |
| `getJob(string $jobId)` | `GET /jobs/{id}` | `Job` | Yes |
| `listAssets()` | `GET /assets` | `Asset[]` | Yes |
| `getAsset(string $assetId)` | `GET /assets/{id}` | `Asset` | Yes |
| `listStorage()` | `GET /storage` | `StorageListItem[]` | Yes |
| `getStorage(string $storageId)` | `GET /storage/{id}` | `StorageDetail` | Yes |
| `addStorage(StorageParams\|array $config)` | `POST /storage` | `StorageDetail\|StorageNotAllowedResponse` | No |
| `deleteStorage(string $storageId)` | `DELETE /storage/{id}` | `void` | No |
| `getAccount()` | `GET /account` | `AccountInfo` | Yes |
| `getStatus()` | `GET /getStatus` | `StatusResponse` | Yes |
| `listTemplates(?array $options)` | `GET /v2/templates` | `PublicTemplate[]` | Yes |
| `getTemplate(string $templateId)` | `GET /v2/template/{id}` | `PublicTemplate` | Yes |

### Utility Methods

| Method | Returns | Throws |
|--------|---------|--------|
| `downloadJobResult(Job $job)` | `string` (binary) | `ValidationException` (no assetUrl), `ApiException` (HTTP error) |
| `waitForJob(string $jobId, array $options = [])` | `Job` | `ValidationException` (interval < 2000ms), `TimeoutException`, `ApiException` (job error) |

**waitForJob options:**
- `interval` (int, default 5000): Poll interval in milliseconds (minimum 2000)
- `maxWait` (int, default 300000): Maximum wait time in milliseconds

## Exceptions

```
\RuntimeException
  └── CloudLayerException
        ├── ConfigException          — Invalid client config
        ├── ValidationException      — Input validation failure ($field property)
        ├── NetworkException         — DNS/connection failure ($requestPath, $requestMethod)
        ├── TimeoutException         — Request timeout ($timeout, $requestPath, $requestMethod)
        └── ApiException             — HTTP 4xx/5xx ($status, $statusText, $body, $requestPath, $requestMethod)
              ├── AuthException      — 401/403
              └── RateLimitException — 429 ($retryAfter)
```

## Enums

| Enum | Values |
|------|--------|
| `ApiVersion` | `V1 = 'v1'`, `V2 = 'v2'` |
| `PdfFormat` | `Letter`, `Legal`, `Tabloid`, `Ledger`, `A0`–`A6` |
| `ImageType` | `Jpg`, `Jpeg`, `Png`, `Svg`, `Webp` |
| `JobStatus` | `Pending`, `Success`, `Error` |

## Response Models

All models are `final readonly class` with `static fromArray(array $data): self`.

| Class | Key Properties |
|-------|---------------|
| `Job` | `id`, `uid`, `status`, `timestamp`, `assetUrl`, `error` |
| `Asset` | `uid`, `id`, `fileId`, `type`, `url`, `size` |
| `StorageListItem` | `id`, `title` |
| `StorageDetail` | `id`, `title`, `uid`, `data` |
| `StorageParams` | `title`, `region`, `accessKeyId`, `secretAccessKey`, `bucket`, `endpoint` |
| `StorageNotAllowedResponse` | `allowed`, `reason`, `statusCode` |
| `AccountInfo` | `email`, `calls`, `callsLimit`, `subType`, `credit`, `extra` |
| `StatusResponse` | `status` |
| `PublicTemplate` | `id`, `extra` |
| `ConversionResult` | `data` (Job\|string), `headers`, `status`, `filename` |
| `ResponseHeaders` | `workerJobId`, `processTime`, `bandwidth`, `callsRemaining`, etc. |
| `ApiErrorBody` | `statusCode`, `error`, `message`, `details` |
