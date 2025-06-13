<?php

namespace Sumihiro\LineWorksClient\Contracts\Bot;

use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\CreateResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\DefaultMenuResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\DeleteResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\DetailResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\GetImageResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\ListResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\SetImageResponse;
use Sumihiro\LineWorksClient\DTO\Bot\RichMenu\UserMenuResponse;

interface RichMenuClientInterface
{
    public function list(): ListResponse;
    
    public function get(string $richMenuId): DetailResponse;
    
    public function create(array $richMenu): CreateResponse;
    
    public function delete(string $richMenuId): DeleteResponse;
    
    public function setForUser(string $accountId, string $richMenuId): UserMenuResponse;
    
    public function getForUser(string $accountId): UserMenuResponse;
    
    public function deleteForUser(string $accountId): UserMenuResponse;
    
    public function setRichMenuImage(string $richMenuId, string $fileId): SetImageResponse;
    
    public function getImage(string $richMenuId): GetImageResponse;
    
    public function uploadImage(string $richMenuId, string $imagePath): SetImageResponse;
    
    public function setDefault(string $richMenuId): DefaultMenuResponse;
    
    public function getDefault(): DefaultMenuResponse;
    
    public function deleteDefault(): DefaultMenuResponse;
}