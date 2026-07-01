<?php

declare(strict_types=1);

namespace App\Bale;

use App\Core\BotBase;

class Bot extends BotBase {
    protected function getDefaultApiBaseUrl(): string {
        return 'https://tapi.bale.ai/bot';
    }

    protected function getDefaultFileBaseUrl(): string {
        return 'https://tapi.bale.ai/file/bot';
    }

    protected function getPlatformName(): string {
        return 'bale';
    }

    public function getFileUrl($fileId) {
        $file = $this->getFile($fileId);
        if ($file && isset($file['file_path'])) {
            return "https://tapi.bale.ai/file/bot{$this->token}/{$file['file_path']}";
        }
        return false;
    }
}
