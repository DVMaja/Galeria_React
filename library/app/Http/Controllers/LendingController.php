<?php

namespace App\Http\Controllers;

use App\Models\Lending;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LendingController extends Controller
{
    public function index()
    {
        return Lending::all();
    }

    public function show($user_id, $copy_id, $start)
    {
        $lending = Lending::where('user_id', $user_id)->where('copy_id', $copy_id)->where('start', $start)->get();
        return $lending[0];
    }


    public function destroy($user_id, $copy_id, $start)
    {
        LendingController::show($user_id, $copy_id, $start)->delete();
    }

    public function update(Request $request, $user_id, $copy_id, $start)
    {
        $lending = Lending::show($user_id, $copy_id, $start);
        // csak patch!!!
        $lending->end = $request->end;
        $lending->extension = $request->extension;
        $lending->notice = $request->notice;
        $lending->save();
    }

    public function store(Request $request)
    {
        $lending = new Lending();
        $lending->user_id = $request->user_id;
        $lending->copy_id = $request->copy_id;
        $lending->start = $request->start;
        $lending->end = $request->end;
        $lending->extension = $request->extension;
        $lending->notice = $request->notice;
        $lending->save();
    }

    public function lendingUser()
    {
        //bejelentkezett felhasználó
        $user = Auth::user();
        $lendings = Lending::with('user')->where('user_id', '=', $user->id)->get();
        return $lendings;
    }

    public function lendingUser2()
    {
        //bejelentkezett felhasználó
        $user = Auth::user();
        $lendings = Lending::with('userHas')->where('user_id', '=', $user->id)->get();
        return $lendings;
    }

    public function booksAtUser()
    {
        $user = Auth::user(); //bejelentkezett
        $books = DB::table('lendings as l')
            ->join('copies as c', 'l.copy_id', '=', 'c.copy_id')
            ->join('books as b', 'c.book_id', '=', 'b.book_id')
            ->select('b.title', 'b.author')
            ->where('l.user_id', '=', $user->id)
            ->whereNull('l.end') //nem nálam van, mert vissza van víve
            ->get();
        return $books;
    }
    //hosszabbítsd meg az egyik nálad lévő könyvet (copy_id, start) ! (patch)
    public function lenghten($copy_id, $start)
    {
        //patch lesz
        $user = Auth::user();
        DB::table('lendings')
            ->where('copy_id', $copy_id)
            ->where('start', $start)
            ->where('user_id', $user->id)
            //az update paramétere az  tömb benne
            ->update(['extension' => 1]);
    }
    //Listázd ki a mai napon visszahozott könyveket!

    public function bringBackBooks()
    {
        //$user = Auth::user();
        $books = DB::table('lendings as l')
            ->whereRaw("DATEDIFF('" . date('Y-m-d') . "', l.end) = 0")
            //->where('user_id', $user->id)
            ->get();

        return $books;
    }
}
