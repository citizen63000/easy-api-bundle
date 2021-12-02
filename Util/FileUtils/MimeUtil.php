<?php

namespace EasyApiBundle\Util\FileUtils;

class MimeUtil
{
    public const MIMES_IMAGE = [
        self::MIME_IMAGE_BMP, self::MIME_IMAGE_CDR, self::MIME_IMAGE_GIF, self::MIME_IMAGE_JP2, self::MIME_IMAGE_JPEG,
        self::MIME_IMAGE_JPM, self::MIME_IMAGE_JPX, self::MIME_IMAGE_MS_BMP, self::MIME_IMAGE_PJPEG, self::MIME_IMAGE_PNG,
        self::MIME_IMAGE_X_PNG, self::MIME_IMAGE_VND_ADOBE_PHOTOSHOP, self::MIME_IMAGE_VND_MICROSOFT_ICON,
        self::MIME_IMAGE_SVG_XML, self::MIME_IMAGE_TIFF, self::MIME_IMAGE_X_BITMAP, self::MIME_IMAGE_X_BMP,
        self::MIME_IMAGE_X_CDR, self::MIME_IMAGE_X_ICO, self::MIME_IMAGE_X_ICON, self::MIME_IMAGE_X_MS_BMP,
        self::MIME_IMAGE_X_WIN_BITMAP, self::MIME_IMAGE_X_WINDOWS_BMP, self::MIME_IMAGE_X_XBITMAP, self::MIME_APPLICATION_BMP,
        self::MIME_APPLICATION_X_BMP, self::MIME_APPLICATION_X_WIN_BITMAP
    ];

    public const MIMES_IMAGE_WEB = [
        self::MIME_IMAGE_GIF, self::MIME_IMAGE_JP2, self::MIME_IMAGE_JPEG, self::MIME_IMAGE_PJPEG, self::MIME_IMAGE_PNG,
        self::MIME_IMAGE_X_PNG, self::MIME_IMAGE_VND_MICROSOFT_ICON, self::MIME_IMAGE_SVG_XML, self::MIME_IMAGE_TIFF,
        self::MIME_IMAGE_X_CDR, self::MIME_IMAGE_X_ICO, self::MIME_IMAGE_X_ICON
    ];

    public const MIMES_AUDIO = [
        self::MIME_AUDIO_AC3, self::MIME_AUDIO_AIFF, self::MIME_AUDIO_MIDI, self::MIME_AUDIO_MP3, self::MIME_AUDIO_MPEG,
        self::MIME_AUDIO_MPEG3, self::MIME_AUDIO_MPG, self::MIME_AUDIO_OGG, self::MIME_AUDIO_WAV, self::MIME_AUDIO_WAVE,
        self::MIME_AUDIO_X_ACC, self::MIME_AUDIO_X_AIFF, self::MIME_AUDIO_X_AU, self::MIME_AUDIO_X_FLAC, self::MIME_AUDIO_X_M4A,
        self::MIME_AUDIO_X_MS_WMA, self::MIME_AUDIO_X_PN_REALAUDIO, self::MIME_AUDIO_X_PN_REALAUDIO_PLUGIN, self::MIME_AUDIO_X_REALAUDIO,
        self::MIME_AUDIO_X_WAV
    ];

    public const MIMES_TEXT_DOCUMENT = [
        self::MIME_TEXT_PLAIN, self::MIME_TEXT_RTF, self::MIME_TEXT_XSL, self::MIME_APPLICATION_XLS, self::MIME_APPLICATION_X_XLS,
        self::MIME_APPLICATION_PDF, self::MIME_APPLICATION_VND_OPENXMLFORMATS_OFFICEDOCUMENT_PRESENTATIONML_PRESENTATION,
        self::MIME_APPLICATION_VND_OPENXMLFORMATS_OFFICEDOCUMENT_SPREADSHEETML_SHEET,
        self::MIME_APPLICATION_VND_OPENXMLFORMATS_OFFICEDOCUMENT_WORDPROCESSINGML_DOCUMENT,
        self::MIME_APPLICATION_VND_MS_EXCEL, self::MIME_APPLICATION_VND_MS_OFFICE, self::MIME_APPLICATION_VND_MS_POWERPOINT,
        self::MIME_APPLICATION_VND_MSEXCEL, self::MIME_APPLICATION_OCTET_STREAM, self::MIME_TEXT_RICHTEXT,
        self::MIME_APPLICATION_POWERPOINT, self::MIME_APPLICATION_MSWORD, self::MIME_APPLICATION_EXCEL,
        self:: MIME_APPLICATION_MSEXCEL , self::MIME_APPLICATION_X_MSEXCEL, self::MIME_APPLICATION_X_MS_EXCEL,
        self::MIME_APPLICATION_X_EXCEL, self::MIME_APPLICATION_X_DOS_MS_EXCEL
    ];

    public const MIMES_VIDEO = [
        self::MIME_VIDEO_3GP, self::MIME_VIDEO_3GPP, self::MIME_VIDEO_AVI, self::MIME_VIDEO_MJ2,
        self::MIME_VIDEO_MP4, self::MIME_VIDEO_3GPP2, self::MIME_VIDEO_MPEG, self::MIME_VIDEO_MSVIDEO,
        self::MIME_VIDEO_OGG, self::MIME_VIDEO_QUICKTIME, self::MIME_VIDEO_VND_RN_REALVIDEO,
        self::MIME_VIDEO_WEBM, self::MIME_VIDEO_X_F4V, self::MIME_VIDEO_X_FLV, self::MIME_VIDEO_X_MS_WMV,
        self::MIME_VIDEO_X_MS_ASF, self::MIME_VIDEO_X_MS_WMV, self::MIME_VIDEO_X_SGI_MOVIE, self::MIME_VIDEO_X_MSVIDEO,
        self::MIME_APPLICATION_X_TROFF_MSVIDEO, self::MIME_APPLICATION_X_DVI
    ];

    public const MIMES_COMPRESSED = [
        self::MIME_APPLICATION_X_GZIP_COMPRESSED, self::MIME_APPLICATION_X_ZIP, self::MIME_APPLICATION_X_COMPRESS,
        self::MIME_APPLICATION_X_COMPRESSED, self::MIME_APPLICATION_S_COMPRESSED, self::MIME_APPLICATION_X_RAR_COMPRESSED,
        self::MIME_APPLICATION_X_ZIP_COMPRESSED, self::MIME_APPLICATION_MAC_COMPACTPRO, self::MIME_APPLICATION_X_ZIP,
        self::MIME_APPLICATION_X_RAR, self::MIME_APPLICATION_X_GZIP, self::MIME_APPLICATION_ZIP, self::MIME_APPLICATION_RAR
    ];

    public const MIME_VIDEO_3GPP2 = 'video/3gpp2';
    public const MIME_VIDEO_3GP = 'video/3gp';
    public const MIME_VIDEO_3GPP = 'video/3gpp';
    public const MIME_APPLICATION_X_COMPRESSED = 'application/x-compressed';
    public const MIME_AUDIO_X_ACC = 'audio/x-acc';
    public const MIME_AUDIO_AC3 = 'audio/ac3';
    public const MIME_APPLICATION_POSTSCRIPT = 'application/postscript';
    public const MIME_AUDIO_X_AIFF = 'audio/x-aiff';
    public const MIME_AUDIO_AIFF = 'audio/aiff';
    public const MIME_AUDIO_X_AU = 'audio/x-au';
    public const MIME_VIDEO_X_MSVIDEO = 'video/x-msvideo';
    public const MIME_VIDEO_MSVIDEO = 'video/msvideo';
    public const MIME_VIDEO_AVI = 'video/avi';
    public const MIME_APPLICATION_X_TROFF_MSVIDEO = 'application/x-troff-msvideo';
    public const MIME_APPLICATION_MACBINARY = 'application/macbinary';
    public const MIME_APPLICATION_MAC_BINARY = 'application/mac-binary';
    public const MIME_APPLICATION_X_BINARY = 'application/x-binary';
    public const MIME_APPLICATION_X_MACBINARY = 'application/x-macbinary';
    public const MIME_IMAGE_BMP = 'image/bmp';
    public const MIME_IMAGE_X_BMP = 'image/x-bmp';
    public const MIME_IMAGE_X_BITMAP = 'image/x-bitmap';
    public const MIME_IMAGE_X_XBITMAP = 'image/x-xbitmap';
    public const MIME_IMAGE_X_WIN_BITMAP = 'image/x-win-bitmap';
    public const MIME_IMAGE_X_WINDOWS_BMP = 'image/x-windows-bmp';
    public const MIME_IMAGE_MS_BMP = 'image/ms-bmp';
    public const MIME_IMAGE_X_MS_BMP = 'image/x-ms-bmp';
    public const MIME_APPLICATION_BMP = 'application/bmp';
    public const MIME_APPLICATION_X_BMP = 'application/x-bmp';
    public const MIME_APPLICATION_X_WIN_BITMAP = 'application/x-win-bitmap';
    public const MIME_APPLICATION_CDR = 'application/cdr';
    public const MIME_APPLICATION_CORELDRAW = 'application/coreldraw';
    public const MIME_APPLICATION_X_CDR = 'application/x-cdr';
    public const MIME_APPLICATION_X_CORELDRAW = 'application/x-coreldraw';
    public const MIME_IMAGE_CDR = 'image/cdr';
    public const MIME_IMAGE_X_CDR = 'image/x-cdr';
    public const MIME_ZZ_APPLICATION_ZZ_WINASSOC_CDR = 'zz-application/zz-winassoc-cdr';
    public const MIME_APPLICATION_MAC_COMPACTPRO = 'application/mac-compactpro';
    public const MIME_APPLICATION_PKIX_CRL = 'application/pkix-crl';
    public const MIME_APPLICATION_PKCS_CRL = 'application/pkcs-crl';
    public const MIME_APPLICATION_X_X509_CA_CERT = 'application/x-x509-ca-cert';
    public const MIME_APPLICATION_PKIX_CERT = 'application/pkix-cert';
    public const MIME_TEXT_CSS = 'text/css';
    public const MIME_TEXT_X_COMMA_SEPARATED_VALUES = 'text/x-comma-separated-values';
    public const MIME_TEXT_COMMA_SEPARATED_VALUES = 'text/comma-separated-values';
    public const MIME_APPLICATION_VND_MSEXCEL = 'application/vnd.msexcel';
    public const MIME_APPLICATION_X_DIRECTOR = 'application/x-director';
    public const MIME_APPLICATION_VND_OPENXMLFORMATS_OFFICEDOCUMENT_WORDPROCESSINGML_DOCUMENT = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    public const MIME_APPLICATION_X_DVI = 'application/x-dvi';
    public const MIME_MESSAGE_RFC822 = 'message/rfc822';
    public const MIME_APPLICATION_X_MSDOWNLOAD = 'application/x-msdownload';
    public const MIME_VIDEO_X_F4V = 'video/x-f4v';
    public const MIME_AUDIO_X_FLAC = 'audio/x-flac';
    public const MIME_VIDEO_X_FLV = 'video/x-flv';
    public const MIME_IMAGE_GIF = 'image/gif';
    public const MIME_APPLICATION_GPG_KEYS = 'application/gpg-keys';
    public const MIME_APPLICATION_X_GTAR = 'application/x-gtar';
    public const MIME_APPLICATION_X_GZIP = 'application/x-gzip';
    public const MIME_APPLICATION_MAC_BINHEX40 = 'application/mac-binhex40';
    public const MIME_APPLICATION_MAC_BINHEX = 'application/mac-binhex';
    public const MIME_APPLICATION_X_BINHEX40 = 'application/x-binhex40';
    public const MIME_APPLICATION_X_MAC_BINHEX40 = 'application/x-mac-binhex40';
    public const MIME_TEXT_HTML = 'text/html';
    public const MIME_IMAGE_X_ICON = 'image/x-icon';
    public const MIME_IMAGE_X_ICO = 'image/x-ico';
    public const MIME_IMAGE_VND_MICROSOFT_ICON = 'image/vnd.microsoft.icon';
    public const MIME_TEXT_CALENDAR = 'text/calendar';
    public const MIME_APPLICATION_JAVA_ARCHIVE = 'application/java-archive';
    public const MIME_APPLICATION_X_JAVA_APPLICATION = 'application/x-java-application';
    public const MIME_APPLICATION_X_JAR = 'application/x-jar';
    public const MIME_IMAGE_JP2 = 'image/jp2';
    public const MIME_VIDEO_MJ2 = 'video/mj2';
    public const MIME_IMAGE_JPX = 'image/jpx';
    public const MIME_IMAGE_JPM = 'image/jpm';
    public const MIME_IMAGE_JPEG = 'image/jpeg';
    public const MIME_IMAGE_PJPEG = 'image/pjpeg';
    public const MIME_APPLICATION_X_JAVASCRIPT = 'application/x-javascript';
    public const MIME_APPLICATION_JSON = 'application/json';
    public const MIME_TEXT_JSON = 'text/json';
    public const MIME_APPLICATION_VND_GOOGLE_EARTH_KML_XML = 'application/vnd.google-earth.kml+xml';
    public const MIME_APPLICATION_VND_GOOGLE_EARTH_KMZ = 'application/vnd.google-earth.kmz';
    public const MIME_TEXT_X_LOG = 'text/x-log';
    public const MIME_AUDIO_X_M4A = 'audio/x-m4a';
    public const MIME_APPLICATION_VND_MPEGURL = 'application/vnd.mpegurl';
    public const MIME_AUDIO_MIDI = 'audio/midi';
    public const MIME_APPLICATION_VND_MIF = 'application/vnd.mif';
    public const MIME_VIDEO_QUICKTIME = 'video/quicktime';
    public const MIME_VIDEO_X_SGI_MOVIE = 'video/x-sgi-movie';
    public const MIME_AUDIO_MPEG = 'audio/mpeg';
    public const MIME_AUDIO_MPG = 'audio/mpg';
    public const MIME_AUDIO_MPEG3 = 'audio/mpeg3';
    public const MIME_AUDIO_MP3 = 'audio/mp3';
    public const MIME_VIDEO_MP4 = 'video/mp4';
    public const MIME_VIDEO_MPEG = 'video/mpeg';
    public const MIME_APPLICATION_ODA = 'application/oda';
    public const MIME_AUDIO_OGG = 'audio/ogg';
    public const MIME_VIDEO_OGG = 'video/ogg';
    public const MIME_APPLICATION_OGG = 'application/ogg';
    public const MIME_APPLICATION_X_PKCS10 = 'application/x-pkcs10';
    public const MIME_APPLICATION_PKCS10 = 'application/pkcs10';
    public const MIME_APPLICATION_X_PKCS12 = 'application/x-pkcs12';
    public const MIME_APPLICATION_X_PKCS7_SIGNATURE = 'application/x-pkcs7-signature';
    public const MIME_APPLICATION_PKCS7_MIME = 'application/pkcs7-mime';
    public const MIME_APPLICATION_X_PKCS7_MIME = 'application/x-pkcs7-mime';
    public const MIME_APPLICATION_X_PKCS7_CERTREQRESP = 'application/x-pkcs7-certreqresp';
    public const MIME_APPLICATION_PKCS7_SIGNATURE = 'application/pkcs7-signature';
    public const MIME_APPLICATION_PDF = 'application/pdf';
    public const MIME_APPLICATION_OCTET_STREAM = 'application/octet-stream';
    public const MIME_APPLICATION_X_X509_USER_CERT = 'application/x-x509-user-cert';
    public const MIME_APPLICATION_X_PEM_FILE = 'application/x-pem-file';
    public const MIME_APPLICATION_PGP = 'application/pgp';
    public const MIME_APPLICATION_X_HTTPD_PHP = 'application/x-httpd-php';
    public const MIME_APPLICATION_PHP = 'application/php';
    public const MIME_APPLICATION_X_PHP = 'application/x-php';
    public const MIME_TEXT_PHP = 'text/php';
    public const MIME_TEXT_X_PHP = 'text/x-php';
    public const MIME_APPLICATION_X_HTTPD_PHP_SOURCE = 'application/x-httpd-php-source';
    public const MIME_IMAGE_PNG = 'image/png';
    public const MIME_IMAGE_X_PNG = 'image/x-png';
    public const MIME_APPLICATION_POWERPOINT = 'application/powerpoint';
    public const MIME_APPLICATION_VND_MS_POWERPOINT = 'application/vnd.ms-powerpoint';
    public const MIME_APPLICATION_VND_MS_OFFICE = 'application/vnd.ms-office';
    public const MIME_APPLICATION_MSWORD = 'application/msword';
    public const MIME_APPLICATION_VND_OPENXMLFORMATS_OFFICEDOCUMENT_PRESENTATIONML_PRESENTATION = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    public const MIME_APPLICATION_X_PHOTOSHOP = 'application/x-photoshop';
    public const MIME_IMAGE_VND_ADOBE_PHOTOSHOP = 'image/vnd.adobe.photoshop';
    public const MIME_AUDIO_X_REALAUDIO = 'audio/x-realaudio';
    public const MIME_AUDIO_X_PN_REALAUDIO = 'audio/x-pn-realaudio';
    public const MIME_APPLICATION_X_RAR = 'application/x-rar';
    public const MIME_APPLICATION_RAR = 'application/rar';
    public const MIME_APPLICATION_X_RAR_COMPRESSED = 'application/x-rar-compressed';
    public const MIME_AUDIO_X_PN_REALAUDIO_PLUGIN = 'audio/x-pn-realaudio-plugin';
    public const MIME_APPLICATION_X_PKCS7 = 'application/x-pkcs7';
    public const MIME_TEXT_RTF = 'text/rtf';
    public const MIME_TEXT_RICHTEXT = 'text/richtext';
    public const MIME_VIDEO_VND_RN_REALVIDEO = 'video/vnd.rn-realvideo';
    public const MIME_APPLICATION_X_STUFFIT = 'application/x-stuffit';
    public const MIME_APPLICATION_SMIL = 'application/smil';
    public const MIME_TEXT_SRT = 'text/srt';
    public const MIME_IMAGE_SVG_XML = 'image/svg+xml';
    public const MIME_APPLICATION_X_SHOCKWAVE_FLASH = 'application/x-shockwave-flash';
    public const MIME_APPLICATION_X_TAR = 'application/x-tar';
    public const MIME_APPLICATION_X_GZIP_COMPRESSED = 'application/x-gzip-compressed';
    public const MIME_IMAGE_TIFF = 'image/tiff';
    public const MIME_TEXT_PLAIN = 'text/plain';
    public const MIME_TEXT_X_VCARD = 'text/x-vcard';
    public const MIME_APPLICATION_VIDEOLAN = 'application/videolan';
    public const MIME_TEXT_VTT = 'text/vtt';
    public const MIME_AUDIO_X_WAV = 'audio/x-wav';
    public const MIME_AUDIO_WAVE = 'audio/wave';
    public const MIME_AUDIO_WAV = 'audio/wav';
    public const MIME_APPLICATION_WBXML = 'application/wbxml';
    public const MIME_VIDEO_WEBM = 'video/webm';
    public const MIME_AUDIO_X_MS_WMA = 'audio/x-ms-wma';
    public const MIME_APPLICATION_WMLC = 'application/wmlc';
    public const MIME_VIDEO_X_MS_WMV = 'video/x-ms-wmv';
    public const MIME_VIDEO_X_MS_ASF = 'video/x-ms-asf';
    public const MIME_APPLICATION_XHTML_XML = 'application/xhtml+xml';
    public const MIME_APPLICATION_EXCEL = 'application/excel';
    public const MIME_APPLICATION_MSEXCEL = 'application/msexcel';
    public const MIME_APPLICATION_X_MSEXCEL = 'application/x-msexcel';
    public const MIME_APPLICATION_X_MS_EXCEL = 'application/x-ms-excel';
    public const MIME_APPLICATION_X_EXCEL = 'application/x-excel';
    public const MIME_APPLICATION_X_DOS_MS_EXCEL = 'application/x-dos_ms_excel';
    public const MIME_APPLICATION_XLS = 'application/xls';
    public const MIME_APPLICATION_X_XLS = 'application/x-xls';
    public const MIME_APPLICATION_VND_OPENXMLFORMATS_OFFICEDOCUMENT_SPREADSHEETML_SHEET = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    public const MIME_APPLICATION_VND_MS_EXCEL = 'application/vnd.ms-excel';
    public const MIME_APPLICATION_XML = 'application/xml';
    public const MIME_TEXT_XML = 'text/xml';
    public const MIME_TEXT_XSL = 'text/xsl';
    public const MIME_APPLICATION_XSPF_XML = 'application/xspf+xml';
    public const MIME_APPLICATION_X_COMPRESS = 'application/x-compress';
    public const MIME_APPLICATION_X_ZIP = 'application/x-zip';
    public const MIME_APPLICATION_ZIP = 'application/zip';
    public const MIME_APPLICATION_X_ZIP_COMPRESSED = 'application/x-zip-compressed';
    public const MIME_APPLICATION_S_COMPRESSED = 'application/s-compressed';
    public const MIME_MULTIPART_X_ZIP = 'multipart/x-zip';
    public const MIME_TEXT_X_SCRIPTZSH = 'text/x-scriptzsh';

    /**
     * @param array $mimes
     * @return array
     */
    public static function getMimesExtentions(array $mimes): array
    {
        $extensions = [];
        foreach ($mimes as $mime) {
            $xtensions = static::mimeToExtensions($mime);
            foreach ($xtensions as $extension) {
                $extensions[] = $extension;
            }
        }

        return $extensions;
    }

    /**
     * @param string $mime
     * @return string[]|null
     */
    public static function mimeToExtensions(string $mime): ?array
    {
        $mime_map = [
            'video/3gpp2' => '3g2',
            'video/3gp' => '3gp',
            'video/3gpp' => '3gp',
            'application/x-compressed' => '7zip',
            'audio/x-acc' => 'aac',
            'audio/ac3' => 'ac3',
            'application/postscript' => 'ai',
            'audio/x-aiff' => 'aif',
            'audio/aiff' => 'aif',
            'audio/x-au' => 'au',
            'video/x-msvideo' => 'avi',
            'video/msvideo' => 'avi',
            'video/avi' => 'avi',
            'application/x-troff-msvideo' => 'avi',
            'application/macbinary' => 'bin',
            'application/mac-binary' => 'bin',
            'application/x-binary' => 'bin',
            'application/x-macbinary' => 'bin',
            'image/bmp' => 'bmp',
            'image/x-bmp' => 'bmp',
            'image/x-bitmap' => 'bmp',
            'image/x-xbitmap' => 'bmp',
            'image/x-win-bitmap' => 'bmp',
            'image/x-windows-bmp' => 'bmp',
            'image/ms-bmp' => 'bmp',
            'image/x-ms-bmp' => 'bmp',
            'application/bmp' => 'bmp',
            'application/x-bmp' => 'bmp',
            'application/x-win-bitmap' => 'bmp',
            'application/cdr' => 'cdr',
            'application/coreldraw' => 'cdr',
            'application/x-cdr' => 'cdr',
            'application/x-coreldraw' => 'cdr',
            'image/cdr' => 'cdr',
            'image/x-cdr' => 'cdr',
            'zz-application/zz-winassoc-cdr' => 'cdr',
            'application/mac-compactpro' => 'cpt',
            'application/pkix-crl' => 'crl',
            'application/pkcs-crl' => 'crl',
            'application/x-x509-ca-cert' => 'crt',
            'application/pkix-cert' => 'crt',
            'text/css' => 'css',
            'text/x-comma-separated-values' => 'csv',
            'text/comma-separated-values' => 'csv',
            'application/vnd.msexcel' => 'csv',
            'application/x-director' => 'dcr',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/x-dvi' => 'dvi',
            'message/rfc822' => 'eml',
            'application/x-msdownload' => 'exe',
            'video/x-f4v' => 'f4v',
            'audio/x-flac' => 'flac',
            'video/x-flv' => 'flv',
            'image/gif' => 'gif',
            'application/gpg-keys' => 'gpg',
            'application/x-gtar' => 'gtar',
            'application/x-gzip' => 'gzip',
            'application/mac-binhex40' => 'hqx',
            'application/mac-binhex' => 'hqx',
            'application/x-binhex40' => 'hqx',
            'application/x-mac-binhex40' => 'hqx',
            'text/html' => 'html',
            'image/x-icon' => 'ico',
            'image/x-ico' => 'ico',
            'image/vnd.microsoft.icon' => 'ico',
            'text/calendar' => 'ics',
            'application/java-archive' => 'jar',
            'application/x-java-application' => 'jar',
            'application/x-jar' => 'jar',
            'image/jp2' => 'jp2',
            'video/mj2' => 'jp2',
            'image/jpx' => 'jp2',
            'image/jpm' => 'jp2',
            'image/jpeg' => ['jpeg', 'jpg'],
            'image/pjpeg' => 'jpeg',
            'application/x-javascript' => 'js',
            'application/json' => 'json',
            'text/json' => 'json',
            'application/vnd.google-earth.kml+xml' => 'kml',
            'application/vnd.google-earth.kmz' => 'kmz',
            'text/x-log' => 'log',
            'audio/x-m4a' => 'm4a',
            'application/vnd.mpegurl' => 'm4u',
            'audio/midi' => 'mid',
            'application/vnd.mif' => 'mif',
            'video/quicktime' => 'mov',
            'video/x-sgi-movie' => 'movie',
            'audio/mpeg' => 'mp3',
            'audio/mpg' => 'mp3',
            'audio/mpeg3' => 'mp3',
            'audio/mp3' => 'mp3',
            'video/mp4' => 'mp4',
            'video/mpeg' => 'mpeg',
            'application/oda' => 'oda',
            'audio/ogg' => 'ogg',
            'video/ogg' => 'ogg',
            'application/ogg' => 'ogg',
            'application/x-pkcs10' => 'p10',
            'application/pkcs10' => 'p10',
            'application/x-pkcs12' => 'p12',
            'application/x-pkcs7-signature' => 'p7a',
            'application/pkcs7-mime' => 'p7c',
            'application/x-pkcs7-mime' => 'p7c',
            'application/x-pkcs7-certreqresp' => 'p7r',
            'application/pkcs7-signature' => 'p7s',
            'application/pdf' => 'pdf',
            'application/octet-stream' => 'pdf',
            'application/x-x509-user-cert' => 'pem',
            'application/x-pem-file' => 'pem',
            'application/pgp' => 'pgp',
            'application/x-httpd-php' => 'php',
            'application/php' => 'php',
            'application/x-php' => 'php',
            'text/php' => 'php',
            'text/x-php' => 'php',
            'application/x-httpd-php-source' => 'php',
            'image/png' => 'png',
            'image/x-png' => 'png',
            'application/powerpoint' => 'ppt',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.ms-office' => 'ppt',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/x-photoshop' => 'psd',
            'image/vnd.adobe.photoshop' => 'psd',
            'audio/x-realaudio' => 'ra',
            'audio/x-pn-realaudio' => 'ram',
            'application/x-rar' => 'rar',
            'application/rar' => 'rar',
            'application/x-rar-compressed' => 'rar',
            'audio/x-pn-realaudio-plugin' => 'rpm',
            'application/x-pkcs7' => 'rsa',
            'text/rtf' => 'rtf',
            'text/richtext' => 'rtx',
            'video/vnd.rn-realvideo' => 'rv',
            'application/x-stuffit' => 'sit',
            'application/smil' => 'smil',
            'text/srt' => 'srt',
            'image/svg+xml' => 'svg',
            'application/x-shockwave-flash' => 'swf',
            'application/x-tar' => 'tar',
            'application/x-gzip-compressed' => 'tgz',
            'image/tiff' => 'tiff',
            'text/plain' => 'txt',
            'text/x-vcard' => 'vcf',
            'application/videolan' => 'vlc',
            'text/vtt' => 'vtt',
            'audio/x-wav' => 'wav',
            'audio/wave' => 'wav',
            'audio/wav' => 'wav',
            'application/wbxml' => 'wbxml',
            'video/webm' => 'webm',
            'audio/x-ms-wma' => 'wma',
            'application/wmlc' => 'wmlc',
            'video/x-ms-wmv' => 'wmv',
            'video/x-ms-asf' => 'wmv',
            'application/xhtml+xml' => 'xhtml',
            'application/excel' => 'xl',
            'application/msexcel' => 'xls',
            'application/x-msexcel' => 'xls',
            'application/x-ms-excel' => 'xls',
            'application/x-excel' => 'xls',
            'application/x-dos_ms_excel' => 'xls',
            'application/xls' => 'xls',
            'application/x-xls' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-excel' => 'xlsx',
            'application/xml' => 'xml',
            'text/xml' => 'xml',
            'text/xsl' => 'xsl',
            'application/xspf+xml' => 'xspf',
            'application/x-compress' => 'z',
            'application/x-zip' => 'zip',
            'application/zip' => 'zip',
            'application/x-zip-compressed' => 'zip',
            'application/s-compressed' => 'zip',
            'multipart/x-zip' => 'zip',
            'text/x-scriptzsh' => 'zsh',
        ];

        return isset($mime_map[$mime]) ? (is_array($mime_map[$mime]) ? $mime_map[$mime] : [$mime_map[$mime]]) : null;
    }
}
