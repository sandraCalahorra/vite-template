<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use App\Models\Ticket;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\SupportTicketMail;

class TicketsController extends Controller
{
    /**
     * Filter tickets based on search criteria and apply pagination.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function filterTickets(Request $request)
    {
        // Start building the query to fetch Ticktes
        $ticket = Ticket::with('files')->select('tickets.*');

        // Apply string search filter if provided
        if ($request->has('stringSearch')) {
            $ticket->where(function ($query) use ($request) {
                // Search by ticket id, subject, name or email
                $query->where('tickets.id', 'LIKE', "%$request->stringSearch%")
                      ->orWhere('tickets.subject', 'LIKE', "%$request->stringSearch%")
                      ->orWhere('tickets.name', 'LIKE', "%$request->stringSearch%")
                      ->orWhere('tickets.email', 'LIKE', "%$request->stringSearch%");
            });
        }

        // Get all tickets, then order the collection by name
        $tickets = $ticket->with('user')->orderBy('created_at', 'desc')->get();
        $orderedData = $tickets->values();

        /* Set pagination parameters */
        $pagination = $this->getPaginationParams($request, 'tickets');
        $page = $pagination['page'];
        $perPage = $request->show_all ? (($tickets->count() > 0) ? $tickets->count() : 10) : $pagination['per_page'];

        // Manually create the paginator using LengthAwarePaginator
        $paginatedData = new LengthAwarePaginator(
            $orderedData->forPage($page, $perPage),
            $orderedData->count(),
            $perPage,
            $page
        );

        return $paginatedData;
    }

    /**
     * Get all tickets with an optional search filter.
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getTickets(Request $request)
    {
        // Start building the query to fetch tickets
        $ticket = Ticket::with(['files', 'user'])->select('tickets.*');

        // Apply string search filter if provided
        if ($request->has('stringSearch')) {
            $ticket->where(function ($query) use ($request) {
                $query->where('tickets.id', 'LIKE', "%$request->stringSearch%")
                      ->orWhere('tickets.subject', 'LIKE', "%$request->stringSearch%")
                      ->orWhere('tickets.name', 'LIKE', "%$request->stringSearch%")
                      ->orWhere('tickets.email', 'LIKE', "%$request->stringSearch%");
            });
        }

        // Fetch all tickets and sort them by ID (natural sorting)
        $allTickets = $ticket->get();
        return $allTickets->sortBy('id', SORT_NATURAL)->values();
    }

    /**
     * Find a specific ticket by its ID and return it.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function findTickets(Request $request, $id)
    {
        // Restrict access if the user is not authorized
        $restrict = 'Not allowed, nice try :)';
        $ticket = Ticket::find($id);

        if (!$ticket) {
            // Return error if the ticket is not found
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        // Get user object from request
        $user_object = $request->attributes->get('user_object');

        // Restrict access based on user level and ownership of the ticket
        if ($user_object->level && $user_object->level >= 30) {
            if ($user_object->id == $id) {
                return response()->json($ticket);
            } else {
                return response()->json(['error' => $restrict], 401);
            }
        } else if (!$user_object) {
            // Return error if the user object is missing
            return response()->json(['error' => $restrict], 401);
        }

        // If no restrictions, return the ticket
        return response()->json($ticket);
    }

    /**
     * Create a new ticket entry.
     *
     * @param Request $request
     * @return mixed
     */
    public function createTickets(Request $request)
    {
        // Restrict access based on user level
        $restrict = 'Not allowed, nice try :)';
        $user_object = $request->attributes->get('user_object');

        if ($user_object->level && $user_object->level > 10) {
            return response()->json(['error' => $restrict], 401);
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }

        // Create a new ticket entry using the request data
        $data = $request->all();
        $data['user_id'] = $user_object->id;

        $ticket = Ticket::create($data);

        // Handle file uploads
        $this->handleFileUploads($request, $ticket);

        // Load files relationship for email attachments
        $ticket->load('files');

        // Send email to support
        try {
            Mail::to(config('mail.support_address'))->send(new SupportTicketMail($ticket));
        } catch (Exception $e) {
            $ticket->update(['email_sent' => false]);
            Log::error("Failed to send support email for ticket ID: {$ticket->id}. Error: " . $e->getMessage());
        }

        return $ticket;
    }

    /**
     * Update an existing ticket entry.
     *
     * @param Request $request
     * @param int $id
     * @return mixed
     */
    public function updateTickets(Request $request, $id)
    {
        // Restrict access based on user level
        $restrict = 'Not allowed, nice try :)';
        $user_object = $request->attributes->get('user_object');

        if ($user_object->level && $user_object->level > 0) {
            return response()->json(['error' => $restrict], 401);
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }

        // Find the ticket by ID and update it with the provided data
        $ticket = Ticket::find($id);
        $ticket->update($request->all());

        if ($request->has('priority')) {
            $ticket->update(['priority' => $request->priority]);
        }

        // Handle file uploads (addition)
        $this->handleFileUploads($request, $ticket);

        return $ticket;
    }

    /**
     * Manage file uploads for a ticket.
     *
     * @param Request $request
     * @param Ticket $ticket
     */
    private function handleFileUploads(Request $request, Ticket $ticket)
    {
        if ($request->hasFile('files')) {
            $user_object = $request->attributes->get('user_object');
            $files = $request->file('files');
            $path = "private/files/tickets/{$ticket->id}";

            foreach ($files as $file) {
                $fileName = $file->getClientOriginalName();
                $file->storeAs($path, $fileName);

                File::create([
                    'name' => $fileName,
                    'user_id' => $user_object->id,
                    'ticket_id' => $ticket->id,
                    'file_path' => "files/tickets/{$ticket->id}/",
                ]);
            }
        }
    }

    /**
     * Delete a ticket entry.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteTickets(Request $request)
    {
        // Restrict deletion based on user level
        $restrict = 'Not allowed, nice try :)';
        $user_object = $request->attributes->get('user_object');

        // Only allow deletion if the user level is below 20
        if ($user_object->level && $user_object->level >= 20) {
            return response()->json(['error' => $restrict], 401);
        } else if (!$user_object) {
            return response()->json(['error' => $restrict], 401);
        }

        // Find the ticket by ID and delete it
        $ticket = Ticket::find($request->id);

        if ($ticket) {
            $ticket->delete();
            return response()->json(["data" => "Ticket $request->id deleted successfully."]);
        } else {
            return response()->json(["error" => "Ticket $request->id does not exist."], 404);
        }
    }

    /**
     * Resend the support ticket email.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function sendEmail(Request $request, $id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        try {
            Mail::to(config('mail.support_address'))->send(new SupportTicketMail($ticket));
            $ticket->update(['email_sent' => true]);
            return response()->json(['data' => 'Email sent successfully']);
        } catch (Exception $e) {
            Log::error("Failed to resend support email for ticket ID: {$id}. Error: " . $e->getMessage());
            return response()->json(['error' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download a file associated with a ticket.
     *
     * @param int $id
     * @param string $fileName
     * @return JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadTicketFile($id, $fileName)
    {
        $filePath = storage_path("app/private/files/tickets/{$id}/{$fileName}");

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        return response()->download($filePath);
    }
}
