<?php
// URLs for different sources
$github_repo = "MEGAMINDMK/VCMP-BROWSER";
$gtainside_url = "https://www.gtainside.com/en/vicecity/mods-28/202284-megamind-s-vcmp-browser/";
$sourceforge_url = "https://sourceforge.net/projects/vcmp-browser/files/stats/timeline";
$libertycity_url = "https://libertycity.net/files/gta-vice-city/215495-megaminds-vcmp-browser.html";
$moddb_url = "https://www.moddb.com/mods/megaminds-vcmp-browser";

// Functions for fetching download counts
function getGitHubDownloadCount($repo_url) {
    $url = "https://api.github.com/repos/$repo_url/releases";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'PHP');
    $response = curl_exec($ch);
    curl_close($ch);

    $releases_data = json_decode($response, true);
    $total_downloads = 0;

    if (is_array($releases_data)) {
        foreach ($releases_data as $release) {
            foreach ($release['assets'] as $asset) {
                $total_downloads += $asset['download_count'];
            }
        }
    }

    return $total_downloads;
}

function getGTAInsideDownloadCount($url) {
    $html = @file_get_contents($url);
    if ($html === false) return 0;

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $query = "//tr[td[contains(text(), 'Downloads:')]]/td[2]";
    $nodeList = $xpath->query($query);

    if ($nodeList->length > 0) {
        preg_match('/(\d+)\s*\|/', $nodeList->item(0)->nodeValue, $matches);
        return !empty($matches[1]) ? intval($matches[1]) : 0;
    }

    return 0;
}

function getSourceForgeDownloadCount($url) {
    $html = @file_get_contents($url);
    if ($html === false) return 0;

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $query = "//li[@class='active']//h3[normalize-space(text())='Downloads']/following-sibling::strong[@id='data-total']";
    $nodeList = $xpath->query($query);

    return ($nodeList->length > 0) ? intval($nodeList->item(0)->nodeValue) : 0;
}

function getLibertyCityDownloadCount($url) {
    $html = @file_get_contents($url);
    if ($html === false) return 0;

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query("//div[contains(@class, 'bl_ ddparent')]//text()[contains(., 'Downloads:')]");

    if ($nodes->length > 0) {
        preg_match('/(\d+)/', $nodes->item(0)->nodeValue, $matches);
        return isset($matches[1]) ? intval($matches[1]) : 0;
    }

    return 0;
}

function getModDBDownloadCount($url) {
    $html = @file_get_contents($url);
    if ($html === false) return 0;

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query("//span[@class='buttoncount']");

    if ($nodes->length > 0) {
        return intval(preg_replace('/[^\d]/', '', $nodes->item(0)->nodeValue));
    }

    return 0;
}

// Fetch download counts
$github_downloads = getGitHubDownloadCount($github_repo);
$gtainside_downloads = getGTAInsideDownloadCount($gtainside_url);
$sourceforge_downloads = getSourceForgeDownloadCount($sourceforge_url);
$libertycity_downloads = getLibertyCityDownloadCount($libertycity_url);
$moddb_downloads = getModDBDownloadCount($moddb_url);

// Calculate total downloads
$total_downloads = $github_downloads + $gtainside_downloads + $sourceforge_downloads + $libertycity_downloads + $moddb_downloads;

// Output JSON for Shields.io
header('Content-Type: application/json');
echo json_encode([
    'schemaVersion' => 1,
    'label' => "MEGAMIND'S VCMP BROWSER Total Downloads",
    'message' => (string)$total_downloads, // Convert to string
    'color' => 'brightgreen'
]);
?>
