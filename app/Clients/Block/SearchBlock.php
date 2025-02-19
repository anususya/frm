<?php

declare(strict_types=1);

namespace App\Clients\Block;

use Core\Block\Block as CoreBlock;

class SearchBlock extends CoreBlock
{
    /**
     * @return string
     */
    public function getSearchResult(): string
    {
        return json_encode($this->getData('searchResult')) ?: '{}';
    }
}
