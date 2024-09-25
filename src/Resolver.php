<?php

namespace Burgerbibliothek\ArkManagementTools;

use Burgerbibliothek\ArkManagementTools\Ncda;
use Burgerbibliothek\ArkManagementTools\Validator;

class Resolver
{

    function __construct()
    {
    }

    /**
     * Normalize Input
     * 
     */
    protected static function normalize($input)
    {

        /** NAAN consists only of betanumeric characters */
        $naan = strtolower($naan);

        /** Remove hyphens and whitespace */
        $baseNameAndSuffixes = explode('/', trim(preg_replace('/[\x{0020}|\x{00a0}|\x{002d}|\x{00ad}|\x{2000}-\x{2015}]/u', '', $baseNameAndSuffixes)));
    
    }

    

    public static function resolve($request, $naan, $baseNameAndSuffixes)
    {

        // Normalize basename and suffixes: remove hyphens and whitespace
        $baseNameAndSuffixes = explode('/', trim(preg_replace('/[\x{0020}|\x{00a0}|\x{002d}|\x{00ad}|\x{2000}-\x{2015}]/u', '', $baseNameAndSuffixes)));
        $baseName = $baseNameAndSuffixes[0];
        $suffixes = implode('/', array_slice($baseNameAndSuffixes, 1));
        $checkZone = $naan . '/' . $baseName;
        $query = $request->getQueryString();
        $queryParams = $request->keys();

        // Try to retrive ARK from database
        $ark = ArkModel::where('ark', $checkZone)->first();

        if ($ark) {

            // If ARK could be retrieved process request
            $uri = $ark->uri . '/' . $suffixes . '?' . $query;

            // Return metadata if ?info inflection is set
            if ($suffixes === '' && isset($queryParams[0]) && $queryParams[0] == 'info') {
                return response($ark->metadata)->header('Content-Type', 'text/plain');
            }

            // Check if ark has an http status set
            if ($ark->status_id) {

                $status = StatusModel::find($ark->status_id);
                $code = $status->code;

                if ($code > 299 && $code < 400) {
                    return redirect()->away($uri, $code);
                }

                return abort($code);
            }

            // Standard redirect
            return redirect()->away($uri, 302);
        } else {

            // Check if NAAN is valid
            if (!Validator::validNaan($naan)) {
                return abort(400, __('errors.invalidNAAN'));
            }

            // Try to retrieve NAAN from database
            $getNaan = NaanModel::where('naan', $naan)->first();

            if ($getNaan) {

                //Check ARK for transcription errors
                $minter = MinterModel::firstWhere('id', $getNaan->minter_settings_id);
                if (!Ncda::verify($checkZone, $minter->xdigits)) {
                    abort(400, __('errors.invalidARK'));
                }

                // If nothing worked out, return 404
                return abort(404, __('errors.notFoundARK'));
            }

            // Redirect to global resolver.
            return redirect()->away('https://n2t.net/ark:' . $checkZone . '/' . $suffixes . '/?' . $query, 301);
        }
    }
}
