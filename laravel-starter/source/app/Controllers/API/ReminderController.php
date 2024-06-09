<?php

namespace App\Controllers\API;
use App\Controllers\Controller;
use App\Models\Reminder;
use App\Rules\FrequencyValidationRule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReminderController extends Controller
{
   
    public function index(): JsonResponse 
    {
        $reminders = Reminder::with('recurrenceRules', 'keywords')->get();
        return response()->json($reminders, 200);
    }
    
    public function read(string $id): JsonResponse 
    {
        $reminder = Reminder::findOrFail($id)->with('recurrenceRules', 'keywords')->get();
        return response()->json($reminder, 200);
    }

    // Get all reminders by user_id 
    public function getByUserId(string $user_id): JsonResponse 
    {
        $reminders = Reminder::where('user_id', $user_id)->with('recurrenceRules', 'keywords')->get();
        return response()->json($reminders, 200);
    }

    // Search reminders by keyword.
  
    public function getByKeyword(Request $request): JsonResponse 
    {
        $validatedData = $request->validate([
            'keyword' => 'required|string',
        ]);

        $reminders = Reminder::where('title', 'like', '%' . $validatedData['keyword'] . '%')->get();
       
        return response()->json($reminders, 200);
    }

    
    // Get reminders for a given date range based on their recurrence rules.

    public function getRemindersForDateRange(Request $request): JsonResponse 
    {
        // Validate that start_date and end_date are valid and end_date is greater than or equal to start_date
        $validatedData = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validatedData['start_date']);
        $endDate = Carbon::parse($validatedData['end_date']);

        $reminders = Reminder::with('recurrenceRules')
            ->get()
            ->filter(function ($reminder) use ($startDate, $endDate) {
                return $reminder->isReminderInDateRange($startDate, $endDate);
            });

        return response()->json($reminders, 200);
    }

    // create a new reminder with recurrence rules and keywords 
    public function create(Request $request): JsonResponse 
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|min:1|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'recurrence_rules' => 'nullable|array', 
            'recurrence_rules.*.type' => 'required|string|in:daily,weekly,monthly,yearly,custom',
            'recurrence_rules.*.frequency' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $type = $request->input("recurrence_rules.$index.type");
                    $rule = new FrequencyValidationRule($type); 
                    if (!$rule->passes($attribute, $value)) { // if validation doesn't pass, return specific error msg
                        $fail($rule->message());
                    }
                },
            ], 
            'recurrence_rules.*.start_date' => 'required|date_format:m/d/Y', 
            'recurrence_rules.*.end_date' => 'nullable|date_format:m/d/Y|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'input validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $createData = [
            'user_id' => $request->user_id, 
            'title' => $request->title, 
            'start_time' => $request->start_time,
            'end_time' => $request->end_time
        ];
        
        $reminder = Reminder::create($createData);
        
        $recurrences = $request->recurrence_rules;
        if (isset($recurrences) && !empty($recurrences)) {
            for ($i = 0; $i < count($recurrences); $i++) {
                $recurrences[$i]['reminder_id'] = $reminder->id;
            }
            $reminder->recurrenceRules()->createMany($recurrences);
        } else {
            // if no recurrence rules were given, create one-time occurrence based on the created_at date of the reminder 
            // (since any reminder will occur at least once)
            $startAndEndDate = Carbon::parse($reminder->created_at);
            $reminder->recurrenceRules()->create([
                'reminder_id' => $reminder->id,
                'type' => 'daily',
                'frequency' => 1,
                'start_date' => $startAndEndDate,
                'end_date' => $startAndEndDate
            ]); 
        }

        // parse keywords from title and create keyword entries in db
        $keywords = $this->generateKeywordsHelper($reminder->title, $reminder->id);

        if (!empty($keywords)) {
            $reminder->keywords()->createMany($keywords);
        }

        return response()->json($reminder->load('recurrenceRules', 'keywords'), 201);
    }

    // Update reminder by id 
    public function update(Request $request, string $id): JsonResponse 
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:1|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'recurrence_rules' => 'nullable|array', 
            'recurrence_rules.*.type' => 'required|string|in:daily,weekly,monthly,yearly,custom',
            'recurrence_rules.*.frequency' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $index = explode('.', $attribute)[1];
                    $type = $request->input("recurrence_rules.$index.type");
                    $rule = new FrequencyValidationRule($type); 
                    if (!$rule->passes($attribute, $value)) { // if validation doesn't pass, return specific error msg
                        $fail($rule->message());
                    }
                },
            ], 
            'recurrence_rules.*.start_date' => 'required|date_format:m/d/Y', 
            'recurrence_rules.*.end_date' => 'nullable|date_format:m/d/Y|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'input validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $updateData = [ 
            'title' => $request->title, 
            'start_time' => $request->start_time,
            'end_time' => $request->end_time
        ];

        $reminder = Reminder::findOrFail($id);
        $updateData['user_id'] = $reminder->user_id;
        $titleBeforeUpdate = $reminder->title;
        $reminder->update($updateData);

        // if reminder title has been updated, generate new keywords for new title 
        if ($titleBeforeUpdate !== $reminder->title) {
            $keywords = $this->generateKeywordsHelper($reminder->title, $reminder->id);
            $reminder->keywords()->delete(); 
            $reminder->keywords()->createMany($keywords); 
        }

        // update recurrence rules - this deletes any existing recurrence rules and replaces them with the new recurrence rules
        $recurrences = $request->recurrence_rules;
        if (isset($recurrences) && !empty($recurrences)) {
            $reminder->recurrenceRules()->delete();
            for ($i = 0; $i < count($recurrences); $i++) {
                $recurrences[$i]['reminder_id'] = $reminder->id;
            }
            $reminder->recurrenceRules()->createMany($recurrences);
            
        } else if (isset($recurrences) && empty($recurrences)){
            $reminder->recurrenceRules()->delete();
            // create the one-time reminder occurence 
            $startAndEndDate = Carbon::parse($reminder->created_at);
            $reminder->recurrenceRules()->create([
                'reminder_id' => $reminder->id,
                'type' => 'daily',
                'frequency' => 1,
                'start_date' => $startAndEndDate,
                'end_date' => $startAndEndDate
            ]); 
            
        }
        
        return response()->json($reminder->load('recurrenceRules', 'keywords'), 200);
    }


    // Delete reminder by id 
   
    public function delete(string $id): JsonResponse 
    {
        $reminder = Reminder::findOrFail($id);
        $reminder->delete();

        return response()->json(['message' => 'Reminder deleted successfully'], 200);
    }

    protected function generateKeywordsHelper($reminder_title, $reminder_id): array
    {
        $keywords = array_filter(explode(" ", $reminder_title), function($var) {
            // returns whether the input word is not an article word (common words such as the, an, and etc)
            $filler_words = array('the', 'a', 'an', 'and');
            return !in_array($var, $filler_words);           
        });
        
        return array_map(function ($keyword) use ($reminder_id) {
            return ['keyword' => $keyword, 'reminder_id' => $reminder_id];
        }, $keywords);
       
    }

}