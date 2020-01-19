<?php
/**
 * AMGHOST - Panel
 * Copyright (c) 2020 Lirim ZM <lirimzm@yahoo.com>.
 */

namespace Amghost\Http\Requests\Server;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Contracts\Config\Repository;
use Amghost\Exceptions\Http\Server\FileSizeTooLargeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Amghost\Contracts\Repository\Daemon\FileRepositoryInterface;
use Amghost\Exceptions\Http\Server\FileTypeNotEditableException;
use Amghost\Exceptions\Http\Connection\DaemonConnectionException;

class UpdateFileContentsFormRequest extends ServerFormRequest
{
    /**
     * Return the permission string to validate this request against.
     *
     * @return string
     */
    protected function permission(): string
    {
        return 'edit-files';
    }

    /**
     * Authorize a request to edit a file.
     *
     * @return bool
     *
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Amghost\Exceptions\Http\Server\FileSizeTooLargeException
     * @throws \Amghost\Exceptions\Http\Server\FileTypeNotEditableException
     * @throws \Amghost\Exceptions\Repository\RecordNotFoundException
     */
    public function authorize()
    {
        if (! parent::authorize()) {
            return false;
        }

        $server = $this->attributes->get('server');
        $token = $this->attributes->get('server_token');

        return $this->checkFileCanBeEdited($server, $token);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Checks if a given file can be edited by a user on this server.
     *
     * @param \Amghost\Models\Server $server
     * @param string                     $token
     * @return bool
     *
     * @throws \Amghost\Exceptions\DisplayException
     * @throws \Amghost\Exceptions\Http\Server\FileSizeTooLargeException
     * @throws \Amghost\Exceptions\Http\Server\FileTypeNotEditableException
     */
    private function checkFileCanBeEdited($server, $token)
    {
        $config = app()->make(Repository::class);
        $repository = app()->make(FileRepositoryInterface::class);

        try {
            $stats = $repository->setServer($server)->setToken($token)->getFileStat($this->route()->parameter('file'));
        } catch (RequestException $exception) {
            switch ($exception->getCode()) {
                case 404:
                    throw new NotFoundHttpException;
                default:
                    throw new DaemonConnectionException($exception);
            }
        }

        if ((! $stats->file && ! $stats->symlink) || ! in_array($stats->mime, $config->get('pterodactyl.files.editable'))) {
            throw new FileTypeNotEditableException(trans('server.files.exceptions.invalid_mime'));
        }

        if ($stats->size > $config->get('pterodactyl.files.max_edit_size')) {
            throw new FileSizeTooLargeException(trans('server.files.exceptions.max_size'));
        }

        $this->attributes->set('file_stats', $stats);

        return true;
    }
}
