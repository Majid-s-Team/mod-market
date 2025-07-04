<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventAttachment;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'required|string',
            'description' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*.url' => 'required|url',
            'attachments.*.type' => 'required|in:image,video',
        ]);

        $event = Event::create([
            ...$data,
            'user_id' => auth()->id()
        ]);

        if (isset($data['attachments'])) {
            foreach ($data['attachments'] as $att) {
                $event->attachments()->create($att);
            }
        }

        return response()->json(['message' => 'Event created', 'event' => $event->load('attachments')]);
    }

    public function index()
    {
        return Event::with(['user', 'attachments', 'interestedUsers'])->latest()->get();
    }

    public function show($id)
    {
        $event = Event::with(['user', 'attachments', 'interestedUsers'])->findOrFail($id);
        return response()->json($event);
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        abort_if($event->user_id !== auth()->id(), 403);

        $data = $request->validate([
            'title' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'required|string',
            'description' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*.url' => 'required|url',
            'attachments.*.type' => 'required|in:image,video',
        ]);

        $event->update($data);

        $event->attachments()->delete();
        foreach ($data['attachments'] ?? [] as $att) {
            $event->attachments()->create($att);
        }

        return response()->json(['message' => 'Event updated', 'event' => $event->load('attachments')]);
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        abort_if($event->user_id !== auth()->id(), 403);
        $event->delete();
        return response()->json(['message' => 'Event deleted']);
    }

    public function changeStatus($id)
    {
        $event = Event::findOrFail($id);
        abort_if($event->user_id !== auth()->id(), 403);
        $event->status = $event->status === 'active' ? 'inactive' : 'active';
        $event->save();
        return response()->json(['message' => 'Status changed', 'status' => $event->status]);
    }

    public function upcoming()
    {
        return Event::where('start_date', '>=', now())->with('attachments')->get();
    }

    public function past()
    {
        return Event::where('end_date', '<', now())->with('attachments')->get();
    }

    public function markInterest($id)
    {
        $event = Event::findOrFail($id);
        $userId = auth()->id();

        if ($event->interestedUsers()->where('user_id', $userId)->exists()) {
            $event->interestedUsers()->detach($userId);
            return response()->json(['message' => 'Interest removed']);
        } else {
            $event->interestedUsers()->attach($userId);
            return response()->json(['message' => 'Interest marked']);
        }
    }

}
