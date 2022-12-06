<?php

namespace App\Http\Controllers;

use App\Models\BookReview;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\carbon;
use Illuminate\Support\Carbon as SupportCarbon;

class BookController extends Controller
{
    public function index()
    {
        //$books = Book::orderBy('title', 'asc')->get();
        $books = Book::with('authors', 'category', 'editorial')->get();
        return $this->getResponse200($books);
    }

    public function store(Request $request)
    {
        $isbn = trim($request->isbn);
        $existIsbn = Book::where('isbn', $isbn)->exists();
        if (!$existIsbn) {
            $book = new Book();
            $book->isbn = $isbn;
            $book->title = $request->title;
            $book->description = $request->description;
            $book->published_date = Carbon::now();
            $book->category_id = $request->category['id'];
            $book->editorial_id = $request->editorial['id'];
            $book->save();
            foreach ($request->authors as $item) {
                $book->authors()->attach($item);
            }
            return $this->getResponse201("Book", "Created", $book);
        } else {
            return $this->getResponse400();
        }
    }

    public function update(Request $request, $id)
    {
        $book = Book::find($id);

        DB::beginTransaction();
        try {

            if ($book) {
                $isbn = trim($request->isbn);
                $isbnOwner = Book::where('isbn', $isbn)->first();
                if (!$isbnOwner || $isbnOwner->id == $book->id) {
                    $book->isbn = $isbn;
                    $book->title = $request->title;
                    $book->description = $request->description;
                    $book->published_date = Carbon::now();
                    $book->category_id = $request->category['id'];
                    $book->editorial_id = $request->editorial['id'];
                    $book->update();
                    //Delete
                    foreach ($book->authors as $item) {
                        $book->authors()->detach($item);
                    }
                    //Add new authors
                    foreach ($request->authors as $item) {
                        $book->authors()->attach($item);
                    }
                    $book = Book::with('category', 'editorial', 'authors')->where('id', $id)->get();
                    return $this->getResponse201("Book", "Updated", $book);
                } else {
                    return $this->getResponse400();
                }
            } else {
                return $this->getResponse404();
            }

            DB::commit();
        } catch (Exception $e) {
            return $this->getResponse500($book);
            DB::rollBack();
        }
    }

    public function show($id)
    {
        $book = Book::find($id);
        if ($book) {
            return $this->getResponse200($book);
        }else{
            return $this->getResponse404();
        }
    }

    public function destroy($id)
    {
        $book = Book::find($id);
        if ($book != null) {
            $book->authors()->detach();
            $book->delete();
            return $this->getResponseDelete200("Book");
        }else {
            return $this->getResponse404();
        }
    }

    public function addBookReview(Request $request){
        DB::beginTransaction();
        try {
            $book = new BookReview();
            $book->comment = trim($request->comment);
            $book->user_id = trim(auth()->user()->id);
            $book->book_id = trim($request->book_id);
            $book->save();

            DB::commit();
            return $this->getResponse200($book);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->getResponse500([$e->getMessage()]);
        }
    }

    public function updateBookReview(Request $request, $id){
        DB::beginTransaction();
        try {
            $book = BookReview::find($request->id);
            if($book->user_id != auth()->user()->id){
                return $this->getResponse403();
            }

            $book->comment = trim($request->comment);
            $book->edited = true;
            $book->user_id = trim(auth()->user()->id);
         //   $book->book_id = trim($request->book_id);
            $book->save();

            DB::commit();
            return $this->getResponse200($book);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->getResponse500([$e->getMessage()]);
        }
    }
}
