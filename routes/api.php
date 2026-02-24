<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\MemberController;
use App\Models\Borrowing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [AuthController::class, 'user']);
    Route::apiResource('authors', AuthorController::class);

    Route::apiResource('books', BookController::class);
    Route::apiResource('members', MemberController::class);

    Route::apiResource('borrowings', BorrowingController::class)->only(['index', 'store', 'show']);

    // return & overdue
    Route::post('borrowings/{borrowing}/return', [BorrowingController::class, 'returnBook']);
    Route::get('borrowings/overdue/list', [BorrowingController::class, 'overdue']);


    Route::get('statistics', function () {
        $totalBooks = \App\Models\Book::count();
        $totalAuthors = \App\Models\Author::count();
        $totalMembers = \App\Models\Member::count();
        $booksBorrowed = Borrowing::where('status', 'borrowed')->count();
        $overdueBorrowings = Borrowing::where('status', 'overdue')->count();

        return response()->json([
            'total_books' => $totalBooks,
            'total_authors' => $totalAuthors,
            'total_members' => $totalMembers,
            'books_borrowed' => $booksBorrowed,
            'overdue_borrowings' => $overdueBorrowings,
        ]);
    });
});
