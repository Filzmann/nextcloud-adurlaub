#!/usr/bin/env bash
set -euo pipefail

: "${ADU_BASE_URL:?ADU_BASE_URL fehlt}"
: "${ADU_USER:?ADU_USER fehlt}"
: "${ADU_PASSWORD:?ADU_PASSWORD fehlt}"
: "${ADU_EXPECTED:?ADU_EXPECTED fehlt}"

response="$(mktemp)"
trap 'rm -f "$response"' EXIT

curl --fail --silent --show-error --insecure --user "$ADU_USER:$ADU_PASSWORD" \
    "$ADU_BASE_URL/index.php/apps/adurlaub/api/teams" --output "$response"

ADU_RESPONSE="$response" php -r '
$state = json_decode(file_get_contents(getenv("ADU_RESPONSE")), true, flags: JSON_THROW_ON_ERROR);
$actual = [];
foreach ($state["teams"] ?? [] as $team) {
    foreach ($team["employees"] ?? [] as $employee) {
        $uid = (string)($employee["uid"] ?? "");
        if ($uid === "") continue;
        $actual[$uid] = [
            "canManage" => (bool)($employee["canManage"] ?? false),
            "canApprove" => (bool)($employee["canApprove"] ?? false),
        ];
    }
}
foreach (explode(",", getenv("ADU_EXPECTED")) as $expectation) {
    [$uid, $raw] = explode("=", $expectation, 2);
    [$visible, $manage, $approve] = explode(":", $raw, 3);
    $isVisible = array_key_exists($uid, $actual);
    if ($isVisible !== ($visible === "true")) {
        fwrite(STDERR, "Sichtbarkeitsvertrag verletzt für {$uid}: erwartet {$visible}." . PHP_EOL);
        exit(1);
    }
    if (!$isVisible) continue;
    foreach (["canManage" => $manage, "canApprove" => $approve] as $key => $expected) {
        if ($expected === "*") continue;
        $expectedValue = $expected === "true";
        if ($actual[$uid][$key] !== $expectedValue) {
            fwrite(STDERR, "Rechtevertrag {$key} verletzt für {$uid}: erwartet {$expected}." . PHP_EOL);
            exit(1);
        }
    }
}
'

echo "AD Urlaub access HTTP smoke: OK ($ADU_USER)"
