<?php

namespace MIT\HomePagePopup\Api\Data;

interface PopupImageManagementInterface
{
    const IMAGE_PATH = 'image_path';

    /**
     * Get image
     * @return string|null
     */
    public function getImagePath();

    /**
     * Set image
     * @param string $image
     * @return $this
     */
    public function setImagePath($image);

}
