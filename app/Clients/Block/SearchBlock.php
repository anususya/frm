<?php

namespace App\Clients\Block;

use Core\Block\Block as CoreBlock;

class SearchBlock extends CoreBlock
{
    /**
     * @return false|string
     */
    public function getSearchResult(): string|false
    {
        return json_encode($this->getData('searchResult'));
    }
}
