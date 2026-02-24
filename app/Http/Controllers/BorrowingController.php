<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBorrowingRequest;
use App\Http\Resources\BorrowingResource;
use App\Models\Book;
use App\Models\Borrowing;
use Illuminate\Http\Request;

class BorrowingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Borrowing::with(['member', 'book']);
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('member_id')) {
            $query->where('member_id', $request->member_id);
        }

        $borrowings = $query->latest()->paginate(15);

        return BorrowingResource::collection($borrowings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBorrowingRequest $request)
    {
        $book = Book::findOrFail($request->book_id);

        if (!$book->isAvailable()) {
            return response()->json([
                'message' => "Book is not available for borrowing."
            ], 422);
        }

        $borrowing = Borrowing::create($request->validated());
        $book->update();
        $borrowing->load(['book', 'member']);

        return new BorrowingResource($borrowing);
    }

    /**
     * Display the specified resource.
     */
    public function show(Borrowing $borrowing)
    {
        $borrowing->load(['book', 'member']);

        return new BorrowingResource($borrowing);
    }

    public function returnBook(Borrowing $borrowing)
    {
        if ($borrowing->status !== 'borrowed') {
            return response()->json([
                'message' => "Book has already be returned",
            ], 422);
        }

        // updating borrowing record
        $borrowing->update([
            'returned_date' => now(),
            'status' => "returned"
        ]);

        // update book availablity
        $borrowing->book->returnBook();

        $borrowing->load(['member', 'book']);

        return new BorrowingResource($borrowing);
    }

    public function overdue()
    {
        $overdueBorrowings = Borrowing::with(['book', 'member'])->where('status', 'borrowed')->where('due_date', '<', now())->get();

        // update status to overdue
        Borrowing::where('status', 'borrowed')->where('due_date', '<', now())->update(['status' => 'overdue']);

        return BorrowingResource::collection($overdueBorrowings);
    }
}
