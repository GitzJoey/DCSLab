<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

trait CacheHelper
{
    private function readFromCache($key): mixed
    {
        $hit = false;
        $tagsArr = [];
        try {
            if (! Config::get('dcslab.DATA_CACHE.ENABLED')) {
                return Config::get('dcslab.ERROR_RETURN_VALUE');
            }

            if (! Cache::tags([auth()->user()->id, class_basename(__CLASS__)])->has($key)) {
                return Config::get('dcslab.ERROR_RETURN_VALUE');
            }

            $hit = true;
            $tagsArr = [auth()->user()->id, class_basename(__CLASS__)];

            return Cache::tags($tagsArr)->get($key);
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);

            return Config::get('dcslab.ERROR_RETURN_VALUE');
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__CLASS__.' '.__FUNCTION__.($hit ? ' Hit' : ' Miss').' Key: '.$key.', Tags: ['.implode(',', $tagsArr).']');
        }
    }

    private function saveToCache($key, $val): void
    {
        $tagsArr = [];
        try {
            if (empty($key)) {
                return;
            }

            $tagsArr = [auth()->user()->id, class_basename(__CLASS__)];
            Cache::tags($tagsArr)->add($key, $val, Config::get('dcslab.DATA_CACHE.CACHE_TIME'));
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__CLASS__.' '.__FUNCTION__.' Key: '.$key.', Tags: ['.implode(',', $tagsArr).']');
        }
    }

    private function flushCache($tags = ''): void
    {
        $tagsArr = [];
        try {
            $tagsArr = [auth()->user()->id, class_basename(__CLASS__)];

            if (! empty($tags)) {
                $tagsArr = str_contains($tags, ',') ? explode(',', $tags) : [$tags];
            }

            Cache::tags($tagsArr)->flush();
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__METHOD__.$e);
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '' : auth()->id()).'] '.__CLASS__.' '.__FUNCTION__.' Tags: ['.implode(',', $tagsArr).']');
        }
    }
}
