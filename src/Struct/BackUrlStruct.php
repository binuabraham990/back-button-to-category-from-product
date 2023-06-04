<?php

namespace NtfxBackFromProductPage\Struct;

use Shopware\Core\Framework\Struct\Struct;

class BackUrlStruct extends Struct {

    /**
     * @var string
     */
    private $backUrl;

    public function __construct(string $backUrl) {
        $this->backUrl = $backUrl;
    }

    /**
     * @return string
     */
    public function getBackUrl(): string {
        return $this->backUrl;
    }

    /**
     * @param string $backUrl
     */
    public function setBackUrl(string $backUrl): void {
        $this->backUrl = $backUrl;
    }

}
