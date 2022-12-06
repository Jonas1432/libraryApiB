<?php

namespace App\Http\Controllers;

use App\Models\BookReview;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookReviewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BookReview  $blog
     * @return \Illuminate\Http\Response
     */
    public function show(BookReview $BookReview)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function edit(BookReview $BookReview)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, BookReview $BookReview)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Blog  $blog
     * @return \Illuminate\Http\Response
     */
    public function destroy(BookReview $BookReview)
    {
        //
    }

    public function addBookReview(Request $request){
        DB::beginTransaction();
        try {
            $book = new BookReview();
            $book->commit = trim($request->commit);
            $book->edited = false;
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

    public function updateBookReview(Request $request){
        DB::beginTransaction();
        try {
            $book = BookReview::find($request->id);
            if($book){
                if($book->user_id != auth()->user()->id){
                    return $this->getResponse403();
                }


                $book->commit = trim($request->commit);
                $book->edited = true;
                $book->user_id = trim(auth()->user()->id);
             //   $book->book_id = trim($request->book_id);
                $book->update();

                DB::commit();
                return $this->getResponse200($book);
            }else{
                return $this->getResponse404();
            }

        } catch (Exception $e) {
            DB::rollBack();
            return $this->getResponse500([$e->getMessage()]);
        }
    }
}
