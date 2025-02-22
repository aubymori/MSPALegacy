<?php
// Modify an SWF to change links from:
// http://www.mspaintadventures.com/?s=<s>&p=<p>
// to:
// /read/<s>/<p>
function fix_swf(string $swf)
{
    // We only support ZLIB compressed SWFs, which seems to be what MSPA always uses.
    if (substr($swf, 0, 3) != "CWS")
        return $swf;

    // There is an 8 byte header before the actual compressed SWF data.
    $inflated = @zlib_decode(substr($swf, 8));
    if ($inflated === false)
        return $swf;

    // Enumerate and replace story links
    $matches = [];
    if (!preg_match_all(
    "/(?:http(?:s|):\/\/|)(?:www\.|)mspaintadventures\.com\/(?:scratch\.php|)\?s=(.*?)&p=([0-9]{6,})/",
    $inflated, $matches, PREG_SET_ORDER))
        return $swf;
    foreach ($matches as $match)
    {
        $new_url = "/read/" . $match[1] . "/" . $match[2];
        // Abort if we can't replace the string adequately (shouldn't happen)
        if (strlen($new_url) > strlen($match[0]))
            return $swf;

        // Pad with zeroes and replace
        $new_url = str_pad($new_url, strlen($match[0]), "\0");
        $count = 1;
        $inflated = str_replace($match[0], $new_url, $inflated, $count);
    }

    // At this point, we've already replaced the story links successfully.
    // If we can't compress, just serve the uncompressed SWF.
    $deflated = @zlib_encode($inflated, ZLIB_ENCODING_DEFLATE);
    if ($deflated === false)
        return "F" . substr($swf, 1, 7) . $inflated;
    
    return substr($swf, 0, 8) . $deflated;
}