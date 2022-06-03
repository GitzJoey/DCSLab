<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

trait CacheHelper {
    private function readFromCache($key): mixed
    {
        $hit = false;
        try {
            if (!Config::get('const.DEFAULT.DATA_CACHE.ENABLED')) return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');

            if (!Cache::tags([auth()->user()->id, class_basename(__CLASS__)])->has($key)) return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');

            $hit = true;
            return Cache::tags([auth()->user()->id, class_basename(__CLASS__)])->get($key);
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__CLASS__.' '.__FUNCTION__.($hit ? ' Hit':' Miss').' Key: '.$key. ', Tags: ['.auth()->user()->id.', '.class_basename(__CLASS__).']');
        }
    }

    private function saveToCache($key, $val): void
    {
        try {
            if (empty($key)) return;

            Cache::tags([auth()->user()->id, class_basename(__CLASS__)])->add($key, $val, Config::get('const.DEFAULT.DATA_CACHE.CACHE_TIME.ENV'));
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__CLASS__.' '.__FUNCTION__.' Key: '.$key. ', Tags: ['.auth()->user()->id.', '.class_basename(__CLASS__).']');
        }
    }

    private function flushCache($tags = ''): void
    {
        try {
            $tagsArr = [auth()->user()->id, class_basename(__CLASS__)];

            if (!empty($tags)) {
                $tagsArr = str_contains($tags, ',') ? explode(',', $tags) : [$tags];    
            } 

            Cache::tags($tagsArr)->flush();
        } catch (Exception $e) {
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
        } finally {
            Log::channel('cachehits')->info('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__CLASS__.' '.__FUNCTION__.' Tags: ['.(is_null(auth()->user()) ? '':auth()->id()).', '.class_basename(__CLASS__).']');
        }
    }
}
