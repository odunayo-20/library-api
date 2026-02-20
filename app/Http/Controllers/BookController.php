<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::with('author')->paginate(10);
        return BookResource::collection($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        $book = Book::create($request->validated());
        $book->load('author');

        return new BookResource($book);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    try {
        $book = Book::findOrFail($id);
        $book->load('author');
        return new BookResource($book);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => "The Book is Not Found!",
        ], 404);
    }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreBookRequest $request, Book $book)
    {
        $book->update($request->validated());
        $book->load('author');

        return new BookResource($book);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {


        try {
            $book = Book::findOrFail($id);

            $book->delete();

            return response()->json([
            'status' => true,
            'message' => "Book is deleted successfully",
        ]);
        } catch (\Exception $th) {
            return response()->json([
            'status' => false,
            'message' => "The book is not found",
        ], 404);
        }

    }
}
