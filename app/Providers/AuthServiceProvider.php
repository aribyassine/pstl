<?php

namespace App\Providers;

use App\Policies\PropositionPolicy;
use App\Policies\QuestionPolicy;
use App\Policies\ResponsePolicy;
use App\Policies\SessionPolicy;
use App\Policies\UePolicy;
use App\Proposition;
use App\Question;
use App\Response;
use App\Session;
use App\Ue;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        Ue::class => UePolicy::class,
        Session::class => SessionPolicy::class,
        Question::class => QuestionPolicy::class,
        Proposition::class => PropositionPolicy::class,
        Response::class =>ResponsePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
