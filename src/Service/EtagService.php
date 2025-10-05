<?php
declare(strict_types=1);

namespace ObjectFoundation\Service;

use ObjectFoundation\Http\{Request, Response};

final class EtagService
{
    public function withEtag(Request $req, string $payload, ?int $lastModifiedTs, Response $base): Response
    {
        $etag = '"' . sha1($payload) . '"';
        $headers = array_merge($base->headers, ['ETag' => $etag]);
        if ($lastModifiedTs) {
            $headers['Last-Modified'] = gmdate('D, d M Y H:i:s', $lastModifiedTs) . ' GMT';
        }

        $inm = $req->header('If-None-Match', '');
        $ims = $req->header('If-Modified-Since', '');
        $imsTs = $ims ? @strtotime($ims) : false;

        if ($inm === $etag || ($imsTs && $lastModifiedTs && $imsTs >= $lastModifiedTs)) {
            return new Response(304, $headers, '');
        }
        return new Response($base->status, $headers, $base->body);
    }
}
