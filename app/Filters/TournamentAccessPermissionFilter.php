<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TournamentAccessPermissionFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $shareSettingModel = model('\App\Models\ShareSettingsModel');
        $tournamentModel = model('\App\Models\TournamentModel');

        $token = $request->getUri()->getSegment(1) == 'api' ? $request->getUri()->getSegment(4) : $request->getUri()->getSegment(3);
        
        $shareSetting = $shareSettingModel->where('token', $token)->first();

        $session = \Config\Services::session();

        if (empty($shareSetting)) {
            $session->setFlashdata(['error' => "This page is not valid."]);
            
            return false;
        }

        $tournament = $tournamentModel->find($shareSetting['tournament_id']);
        
        /** Check if the tournament was created by authorized user */
        if (auth()->user() && $tournament && $tournament['user_id'] == auth()->user()->id) {
            $session->set(['share_permission' => $shareSetting['permission']]);
            return;
        }

        /** Check if the sharing is public to everyone */
        if ($shareSetting && $shareSetting['target'] == SHARE_TO_PUBLIC) {
            $session->set(['share_permission' => $shareSetting['permission']]);
            return;
        }

        /** Check if the sharing to everyone with the link */
        if ($shareSetting && $shareSetting['target'] == SHARE_TO_EVERYONE) {
            if (auth()->user()) {
                $session->set(['share_permission' => $shareSetting['permission']]);
            return;
            }
        }

        /** Check if the sharing to everyone with the link */
        if ($shareSetting && $shareSetting['target'] == SHARE_TO_USERS) {
            $users = explode(',', $shareSetting['users']);

            if (auth()->user() && in_array(auth()->user()->id, $users)) {
                $session->set(['share_permission' => $shareSetting['permission']]);
                return;
            }
        }

        /** If there is a share setting but user not authorized */
        if (auth()->user()) {
            $session->setFlashdata(['error' => "You don't have permission to view this tournament!"]);
            $session->setTempdata('beforeLoginUrl', current_url(), 300);
            
            return redirect()->route('tournaments');
        }

        if ($shareSetting && $shareSetting['target'] == SHARE_TO_EVERYONE) {
            $session->setFlashdata(['error' => "It looks like a tournament link was shared with you. To proceed, please signin first."]);
            $session->setTempdata('beforeLoginUrl', current_url(), 300);

            return redirect()->route('login');
        }

        if ($shareSetting && $shareSetting['target'] == SHARE_TO_USERS) {
            $session->setFlashdata(['error' => "It looks like a private tournament link was shared with you. To proceed, please signin first to validate your identity."]);
            $session->setTempdata('beforeLoginUrl', current_url(), 300);

            return redirect()->route('login');
        }

        $session->setFlashdata(['error' => "You don't have permission to view this tournament!"]);
        $session->setTempdata('beforeLoginUrl', current_url(), 300);
        
        return redirect()->route('login');
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}