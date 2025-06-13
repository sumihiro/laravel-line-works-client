<?php

namespace Sumihiro\LineWorksClient\Contracts\Bot;

interface BotClientInterface
{
    public function channel(): ChannelClientInterface;
    
    public function message(): MessageClientInterface;
    
    public function richMenu(): RichMenuClientInterface;
    
    public function attachment(): AttachmentClientInterface;
    
    public function management(): BotManagementClientInterface;
    
    public function monitoring(): MessageContentsClientInterface;
}