<?php

namespace App\Controllers\API;
use App\Controllers\Controller;
use App\Models\Reminder;
use App\Rules\FrequencyValidationRule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function getRemindersForDateRange(Request $request): Response 
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
    public function create(Request $request): Response 
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|min:1|max:255',
            'start_time' => 'required|datetime',
            'end_time' => 'nullable|datetime|after:start_time',
            'recurrence_rules' => 'array', 
            'recurrence_rules.*.type' => 'required|string|in:daily,weekly,monthly,yearly,custom',
            'recurrence_rules.*.frequency' => [
                'required',
                'integer',
                new FrequencyValidationRule($request->input('recurrence_rules.*.type')),
            ], 
            'recurrence_rules.*.start_date' => 'required|date_format:m/d/Y', 
            'recurrence_rules.*.end_date' => 'nullable|date_format:m/d/Y|after_or_equal:start_date',
        ]);
    
        $reminder = Reminder::create($validatedData['user_id'], $validatedData['title'], $validatedData['start_time'], $validatedData['end_time']);
        
        
        for ($i = 0; $i < $validatedData['recurrence_rules'].length; $i++) {
            $validatedData['recurrence_rules'][$i]['reminder_id'] = $reminder->id;
        }

        if (isset($validatedData['recurrence_rules']) && !empty($validatedData['recurrence_rules'])) {
            $reminder->recurrenceRules()->createMany($validatedData['recurrence_rules']);
        } 

        // parse keywords from title and create keyword entries in db
        $keywords = $this->generateKeywordsHelper($reminder->title, $reminder->id);

        if (!empty($keywords)) {
            $reminder->keywords()->createMany($keywords);
        }

        return response()->json($reminder->load('recurrenceRules'), 201);
    }

    // Update reminder by id 
    public function update(Request $request, string $id): Response 
    {
        $validatedData = $request->validate([
            'title' => 'required|string|min:1|max:255',
            'start_time' => 'required|datetime',
            'end_time' => 'nullable|datetime|after:start_time',
            'recurrence_rules' => 'array', 
            'recurrence_rules.*.type' => 'required|in:daily,weekly,monthly,yearly,custom',
            'recurrence_rules.*.frequency' => [
                'required',
                'integer',
                new FrequencyValidationRule($request->input('recurrence_rules.*.type')),
            ], 
            'recurrence_rules.*.start_date' => 'required|date_format:m/d/Y', 
            'recurrence_rules.*.end_date' => 'nullable|date_format:m/d/Y|after_or_equal:start_date',
        ]);
        $reminder = Reminder::findOrFail($id);
        $title_before_update = $reminder->title;
        $reminder->update($validatedData['title'], $validatedData['start_time'], $validatedData['end_time']);

        // if reminder title has been updated, generate new keywords for new title 
        if ($title_before_update !== $reminder->title) {
            $keywords = $this->generateKeywordsHelper($reminder->title, $reminder->id);
            $reminder->keywords()->delete(); 
            $reminder->keywords()->createMany($keywords); 
        }

        // update recurrence rules 
        if (isset($validatedData['recurrence_rules'])) {
            $reminder->recurrenceRules()->delete();
            if (!empty($validatedData['recurrence_rules'])) {
                $reminder->recurrenceRules()->createMany($validatedData['recurrence_rules']);
            }
        }

        return response()->json($reminder->load('recurrenceRules'), 200);
    }


    // Delete reminder by id 
   
    public function delete(string $id): Response 
    {
        $reminder = Reminder::findOrFail($id);
        $reminder->delete();

        return response()->json(['message' => 'Reminder deleted successfully'], 200);
    }

    protected function generateKeywordsHelper($reminder_title, $reminder_id): array
    {
        function nonArticleWord($var) {
            // returns whether the input word is not an article word (common words such as the, an, and etc)
            $filler_words = array('the', 'a', 'an', 'and');
            return !in_array($var, $filler_words);           
        } 

        $keywords = array_filter(explode(" ", $reminder_title), "nonArticleWord");
        for ($i = 0; $i < $keywords.length; $i++) {
            $keywords[$i]['reminder_id'] = $reminder_id;
        }

        return $keywords;
    }

}