<?php


namespace WindApi\session\driver\options;


class FileOptions implements Options
{
    private $save_path = '';

    private $session_file_ext = ".sfile";

    private $session_file_prefix = 'session_';

    /**
     * @param string $session_file_ext
     * @return $this
     */
    public function setSessionFileExt($session_file_ext)
    {
        $this->session_file_ext = $session_file_ext;
        return $this;
    }

    /**
     * @return string
     */
    public function getSessionFileExt()
    {
        return $this->session_file_ext;
    }

    /**
     * @param string $session_file_prefix
     * @return $this
     */
    public function setSessionFilePrefix($session_file_prefix)
    {
        $this->session_file_prefix = $session_file_prefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getSessionFilePrefix()
    {
        return $this->session_file_prefix;
    }

    /**
     * @param string $save_path
     * @return $this
     */
    public function setSavePath($save_path)
    {
        $this->save_path = $save_path;
        return $this;
    }

    /**
     * @return string
     */
    public function getSavePath()
    {
        return $this->save_path;
    }

    public function toArray()
    {
        return [
            'save_path' => $this->save_path,
            'session_file_prefix' => $this->session_file_prefix,
            'session_file_ext' => $this->session_file_ext
        ];
    }
}