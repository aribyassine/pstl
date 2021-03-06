<?php

namespace App\Http\Controllers;

use App\Proposition;
use App\Question;
use App\User;
use Illuminate\Support\Collection;

class StatController extends Controller
{
    /**
     * @param $id
     */
    public function question($id)
    {
        $question = Question::findOrFail($id);
        $propositions = $question->propositions()->select(['id', 'verdict', 'number', 'title'])->get();
        $responses = $question->responses()->select(['response', 'user_id'])->get();
        $propositions->push(new Proposition(['title' => 'sans opinion', 'number' => 0, 'verdict' => 0]));

        $propositions_true_count = 0;
        foreach ($propositions as $proposition) {
            if ($proposition->verdict)
                $propositions_true_count++;
            $proposition->stat = new Collection();
            if (isset($proposition->id)) {
                $proposition_responses = $responses->where('response', $proposition->number);
            } else {
                $proposition_responses = $responses->where('response', null);
            }
            $users_ids = $proposition_responses->pluck('user_id');
            $proposition->stat["responses_count"] = $proposition_responses->count();
            $proposition->stat["users"] = User::select(['id', 'firstName', 'lastName', 'username'])->find($users_ids->toArray());
        }
        $question->propositions_true_count = $propositions_true_count;
        $question->propositions = $propositions;
        // todo sans opinion
        return $question;
    }

    /**
     * @param $id
     */
    public function question_tour($id)
    {
        try {
            $question = Question::findOrFail($id);
            $propositions = $question->propositions()->select(['id', 'verdict', 'number', 'title'])->get();
            $responses = $question->responses()->select(['response', 'user_id'])->get();

            $user_rep = $responses->groupBy('user_id');
            try {
                $tours_count = max($user_rep->map(function ($item) {
                    return $item->count();
                })->values()->toArray());
            } catch (\Exception $e) {
                $tours_count = 0;
            }
            for ($i = 1; $i <= $tours_count; $i++) {
                $tours[$i] = $user_rep->reduce(function ($carry, $item) use ($i) {
                    if (isset($item[$i - 1]))
                        array_push($carry, $item[$i - 1]);
                    return $carry;
                }, []);
            }
            $propositions_true_count = 0;
            foreach ($propositions as $proposition) {
                if ($proposition->verdict)
                    $propositions_true_count++;
                $proposition->stat = new Collection();
                $proposition->stat["responses_count"] = $responses->where('response', $proposition->number)->count();
            }

            foreach ($propositions as $proposition) {
                $tour = [];
                foreach ($tours as $key => $value) {
                    $responses = collect($value)->filter(function ($value) use ($proposition) {
                        return $value->response == $proposition->number;
                    });
                    $tour = array_add(
                        $tour,
                        $key,
                        ["users" =>
                            User::select(['id', 'firstName', 'lastName', 'username'])
                                ->find($responses->values()->map(function ($response) {
                                    return $response->user_id;
                                })->toArray())
                            ,
                            "count" => $responses->count()
                        ]
                    );
                }

                $proposition->stat["tour"] = $tour;
            }
            $question->propositions_true_count = $propositions_true_count;
            $question->propositions = $propositions;
            return $question;
        } catch (ModelNotFoundException $exception) {
            abort(404, "Not found question with id $id");
        }
    }
}
