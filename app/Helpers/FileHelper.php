<?php

namespace App\Helpers;

class FileHelper
{
    /**
     * Get file size in human readable format
     */
    public static function formatFileSize($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get file extension
     */
    public static function getExtension($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    /**
     * Get file icon class based on extension
     */
    public static function getFileIcon($filename)
    {
        $extension = self::getExtension($filename);
        
        $icons = [
            'pdf' => 'bi-file-earmark-pdf text-danger',
            'doc' => 'bi-file-earmark-word text-primary',
            'docx' => 'bi-file-earmark-word text-primary',
            'xls' => 'bi-file-earmark-excel text-success',
            'xlsx' => 'bi-file-earmark-excel text-success',
            'ppt' => 'bi-file-earmark-ppt text-warning',
            'pptx' => 'bi-file-earmark-ppt text-warning',
            'jpg' => 'bi-file-earmark-image text-info',
            'jpeg' => 'bi-file-earmark-image text-info',
            'png' => 'bi-file-earmark-image text-info',
            'gif' => 'bi-file-earmark-image text-info',
            'zip' => 'bi-file-earmark-zip text-secondary',
            'rar' => 'bi-file-earmark-zip text-secondary',
            'txt' => 'bi-file-earmark-text text-dark',
        ];
        
        return $icons[$extension] ?? 'bi-file-earmark text-muted';
    }

    /**
     * Validate file type for tugas
     */
    public static function isValidTugasFile($filename)
    {
        $extension = strtolower(self::getExtension($filename));
        
        $allowedExtensions = [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar', 'txt'
        ];
        
        return in_array($extension, $allowedExtensions);
    }

    /**
     * Generate unique filename
     */
    public static function generateUniqueFilename($originalFilename)
    {
        $extension = self::getExtension($originalFilename);
        $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '', $basename);
        
        return $basename . '_' . time() . '_' . uniqid() . '.' . $extension;
    }

    /**
     * Sanitize filename
     */
    public static function sanitizeFilename($filename)
    {
        $extension = self::getExtension($filename);
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        
        // Remove special characters
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
        
        return $basename . '.' . $extension;
    }
}