<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventAttachment;
use Illuminate\Support\Facades\Validator;
use \App\Traits\ApiResponseTrait;
class EventController extends Controller
{
    use ApiResponseTrait;


    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
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

        return $this->apiResponse('Event created successfully', [
            'event' => $event->load('attachments'),
        ]);
    }

    // public function index()
    // {
    //     $events = Event::with(['user', 'attachments', 'interestedUsers'])->latest()->get();
    //     return $this->apiResponse('All events fetched successfully', [
    //         'events' => $events
    //     ]);
    // }
    // public function index(Request $request)
    // {
    //     $perPage = $request->get('per_page', 10);
    //     $events = Event::with(['user', 'attachments', 'interestedUsers'])->latest()->paginate(10);
    //     return $this->apiPaginatedResponse('All events fetched successfully', $events);
    // }
    // public function index(Request $request)
    // {
    //     $perPage = $request->get('per_page', 10);
    //     $search = $request->get('search', $request->query('search', ''));

    //     $events = Event::with(['user', 'attachments', 'interestedUsers'])->where($search)
    //         ->latest()
    //         ->paginate($perPage);

    //     return $this->apiPaginatedResponse('All events fetched successfully', $events);
    // }
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        $events = Event::with(['user', 'attachments', 'interestedUsers'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate($perPage);

        return $this->apiPaginatedResponse('All events fetched successfully', $events);
    }


    public function show($id)
    {
        $event = Event::with(['user', 'attachments', 'interestedUsers'])->findOrFail($id);
        if (!$event) {
            return $this->apiError('Event not found', [], 404);
        }
        return $this->apiResponse('Event fetched successfully', [
            'event' => $event
        ]);

    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        // abort_if($event->user_id !== auth()->id(), 403);

        $data = $request->validate([
            'title' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'required',

            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',

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

        // return response()->json(['message' => 'Event updated', 'event' => $event->load('attachments')]);
        return $this->apiResponse('Event updated successfully', [
            'event' => $event->load('attachments')
        ]);
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        abort_if($event->user_id !== auth()->id(), 403);
        $event->delete();
        // return response()->json(['message' => 'Event deleted']);
        return $this->apiResponse('Event deleted successfully');

    }

    public function changeStatus($id)
    {
        $event = Event::findOrFail($id);
        abort_if($event->user_id !== auth()->id(), 403);
        $event->status = $event->status === 'active' ? 'inactive' : 'active';
        $event->save();
        // return response()->json(['message' => 'Status changed', 'status' => $event->status]);
        return $this->apiResponse('Status changed successfully', [
            'status' => $event->status
        ]);
    }

    // public function upcoming()
    // {
    //     $events = Event::where('start_date', '>=', now())
    //         ->with('attachments')
    //         ->latest()
    //         ->paginate(10);

    //     return $this->apiPaginatedResponse('Upcoming events fetched successfully', $events);
    // }
    public function upcoming(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $events = Event::where('end_date', '>=', now())
            ->with('attachments')
            ->orderBy('start_date', 'asc')
            ->paginate($perPage);

        return $this->apiPaginatedResponse('Upcoming events fetched successfully', $events);
    }

    public function past(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $events = Event::where('end_date', '<', now())->with('attachments')->latest()->paginate($perPage);

        return $this->apiPaginatedResponse('Past events fetched successfully', $events);
    }
    public function markInterest($id)
    {
        $event = Event::find($id);
        if (!$event) {
            return $this->apiError('Event not found', [], 404);
        }

        $userId = auth()->id();

        if ($event->interestedUsers()->where('user_id', $userId)->exists()) {
            $event->interestedUsers()->detach($userId);
            return $this->apiResponse('Interest removed successfully');
        } else {
            $event->interestedUsers()->attach($userId);
            return $this->apiResponse('Interest marked successfully');
        }
    }

}
