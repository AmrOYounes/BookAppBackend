<?php

namespace App\Http\Controllers;

use App\Exports\BooksExport;
use App\Exports\OrdersExport;
use App\Models\Author;
use App\Models\Book;
use App\Models\Bookfile;
use App\Models\Order;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Publisher;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BookController extends Controller
{
    //
    protected  $dataToExport = null;
    public function addPublisher(Request $request) {

        $validated = $request->validate([
            'Publisher_name' => 'required',
            'Establish_date' => 'required',
            'Is_working' => 'required',
        ]);

//        dd($validated);
        Publisher::create([
            'Publisher_name' => $request->Publisher_name,
            'Establish_date' => $request->Establish_date,
            'Is_working' => $request->Is_working,
        ]);

        return response()->json(['success' => true], 201);
    }

    public function addAuthors(Request $request) {
        $validated = $request->validate([
            'First_name' => 'required',
            'Middle_name' => 'required',
            'Last_name' => 'required',
            'Birth_date' => 'required',
            'Country_of_residence' => 'required',
            'Death_date' => 'required',
            'Offical_website' => 'required',
        ]);

        Author::create([
            'First_name' => $request->First_name,
            'Middle_name' => $request->Middle_name,
            'Last_name' => $request->Last_name,
            'Birth_date' => $request->Birth_date,
            'Country_of_residence' => $request->Country_of_residence,
            'Death_date' => $request->Death_date,
            'Offical_website' => $request->Offical_website,

        ]);
        return response()->json(['success' => true], 201);
    }

    public function addBook(Request $request){

        $validated = $request->validate([
            'Book_id' => 'required',
            'Book_title' => 'required',
            'publisher_Id' => 'required',
            'Publish_date' => 'required',
            'author_Id' => 'required',
            'Available_units' => 'required',
            'Unit_price' => 'required',
        ]);

        $newBook =  Book::create([
            'Book_id' => $request->Book_id,
            'Book_title' => $request->Book_title,
            'publisher_Id' => $request->publisher_Id,
            'Publish_date' => $request->Publish_date,
            'author_Id' => $request->author_Id,
            'Available_units' => $request->Available_units,
            'Unit_price' => $request->Unit_price,
        ]);



//  print_r($request->file('Book_path'));
        $store_path = 'public/uploads';
//        $file = $request->file('Book_path');
//        $name = $file->getClientOriginalName();
//        $ext = $file->getClientOriginalExtension();
//        $path = $file->storeAs($store_path, $name);
//          dd($request->file('Book_path'));
        if($request->hasfile('Book_path'))
        {

            foreach($request->file('Book_path') as $file)
            {
                $name =$file->getClientOriginalName();
                 $ext = $file->getClientOriginalExtension();
                 $fileNameWithExt = $name.'.'.$ext;
                $path = $file->storeAs($store_path, $name);

                Bookfile::create([
                    'Book_path' => $path,
                    'Book_id' => $newBook->Book_id,
                ]);
            }
        }

        if(isset($request->tags)){
           foreach ($request->tags as $tag){
               Tag::create([
                   'Tag_name' => $tag,
                   'Book_id'=> $newBook->Book_id,
               ]);
           }

        }


//
//       Tag::create([
//          'Tag_name' => $request->tags,
//           'Book_id'=> $newBook->Book_id,
//       ]);

        return response()->json(['success' => true], 201);
    }
    public function  getPublishers(){
        $result = Publisher::all();

        return response()->json(['success' => 'true', 'data' => $result]);
    }

    public function getAuthors() {
      $result = Author::all();
        return response()->json(['success' => 'true', 'data' => $result]);
    }

    public function export(Request $request)
    {
        if($request->type == 'PDF'){

            return Excel::download(new  BooksExport($request), 'books.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
        }
        elseif ($request->type == 'CSV') {
            return Excel::download (new BooksExport($request),'books.csv', \Maatwebsite\Excel\Excel::CSV);
        }
        else{
            return Excel::download (new BooksExport($request),'books.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        }

    }

    public function Ordersexport(Request $request)
    {
        if($request->type == 'PDF'){

            return Excel::download(new  OrdersExport($request), 'orders.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
        }
        elseif ($request->type == 'CSV') {
            return Excel::download (new OrdersExport($request),'orders.csv', \Maatwebsite\Excel\Excel::CSV);
        }
        else{
            return Excel::download (new OrdersExport($request),'orders.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        }
    }

    public  function  searchOrder(Request $request){
//        $order = Order::with('book')->get();
//        return response()->json(['result' => $order], 200);
//        dd($order);
        $searchFilter = [
            'Book title' => 'Book_title',
            'Book Publisher' => 'Book_publisher',
            'Book Author' => 'Book_author',
            'Buyer name' => 'buyerName',
        ];
        $searchData = null;
        if( $request->column_filter == 'Any') {
            $searchData = Order::with('book', 'book.author', 'book.publisher')->
            where('numberOfUnits', 'LIKE', '%' . $request->search_by_word . '%')
                ->orWhere('buyerName', 'LIKE', '%' . $request->search_by_word . '%')
                ->orWhere('totalPrice', 'LIKE', '%' . $request->search_by_word . '%')
                ->orWhereHas('book', function ($query) use ($request) {
                    $query->where('Book_id', 'LIKE', '%' . $request->search_by_word . '%')
                        ->orWhere('Book_title', 'LIKE', '%' . $request->search_by_word . '%');
                })->orWhereHas('book.publisher', function ($query) use ($request) {
                    $query->where('Publisher_name', 'LIKE', '%' . $request->search_by_word . '%');

                })->orWhereHas('book.author', function ($query) use ($request) {
                    $query->where('First_name', 'LIKE', '%' . $request->search_by_word . '%')
                        ->orWhere('Middle_name', 'LIKE', '%' . $request->search_by_word . '%')
                        ->orWhere('Last_name', 'LIKE', '%' . $request->search_by_word . '%');
                });
            if (isset($request->purchaseDate)) {
                $searchData = $searchData->where('purchaseDate', $request->purchaseDate);
            }
            return response()->json(['data' => $searchData->paginate(4)], 200);

        }


        elseif ($searchFilter[$request->column_filter] == 'buyerName'){
            $searchData = Order::with('book','book.author','book.publisher')->
                where('buyerName','LIKE','%'.$request->search_by_word.'%');

            if(isset($request->purchaseDate)){
                $searchData= $searchData->where('purchaseDate',$request->purchaseDate);
            }
            return response()->json(['data' => $searchData->paginate(4)], 200);
        }


        elseif ($searchFilter[$request->column_filter] == 'Book_title'){
            $searchData = Order::with('book','book.author','book.publisher')->
            whereHas('book', function ($query) use($request){
                $query->where('Book_title','LIKE','%'.$request->search_by_word.'%');
            });

            if(isset($request->purchaseDate)){
                $searchData= $searchData->where('purchaseDate',$request->purchaseDate);
            }
            return response()->json(['data' => $searchData->paginate(4)], 200);
        }


        elseif ($searchFilter[$request->column_filter] == 'Book_publisher'){
            $searchData = Order::with('book','book.author','book.publisher')->
            whereHas('book.publisher', function ($query) use($request){
                $query->where('Publisher_name','LIKE','%'.$request->search_by_word.'%');
            });

            if(isset($request->purchaseDate)){
                $searchData= $searchData->where('purchaseDate',$request->purchaseDate);
            }
            return response()->json(['data' => $searchData->paginate(4)], 200);
        }

        else{
            $searchData = Order::with('book','book.author','book.publisher')->
            whereHas('book.author', function ($query) use($request){
                $query->where('First_name','LIKE','%'.$request->search_by_word.'%')
                ->orWhere('Middle_name','LIKE','%'.$request->search_by_word.'%')
                ->orWhere('Last_name','LIKE','%'.$request->search_by_word.'%');
            });

            if(isset($request->purchaseDate)){
                $searchData= $searchData->where('purchaseDate',$request->purchaseDate);
            }
            return response()->json(['data' => $searchData->paginate(4)], 200);
        }
    }

    public function search(Request $request)
    {
        $searchFilter = [
            'Book title' => 'Book_title',
            'Book Publisher' => 'Book_publisher',
            'Book Author' => 'Book_author',
            'tags' => 'Tag_name',

        ];
        $validated = $request->validate([
            'search_by_word' => 'required',
            'column_filter' => 'required',
        ]);
        if ($request->column_filter == 'Any') {

            $result = Book::with('publisher', 'author', 'tag')
                ->where('Book_id', 'LIKE', '%' . $request->search_by_word . '%')
                ->orWhere('Book_title', 'LIKE', '%' . $request->search_by_word . '%')
                ->orWhere('Publish_date', 'LIKE', '%' . $request->search_by_word . '%')
                ->orWhereHas('publisher', function ($query) use ($request) {
                    $query->where('Publisher_name', 'LIKE', '%' . $request->search_by_word . '%');
                })->orWhereHas('author', function ($query) use ($request) {
                    $query->where('First_name', 'LIKE', '%' . $request->search_by_word . '%')
                        ->orWhere('Middle_name', 'LIKE', '%' . $request->search_by_word . '%')
                        ->orWhere('Last_name', 'LIKE', '%' . $request->search_by_word . '%');
                })->orWhereHas('tag', function ($query) use ($request) {
                    $query->where('Tag_name', 'LIKE', '%' . $request->search_by_word . '%');
                })->paginate(4);


            return response()->json(['data' => $result], 200);

//         $book = Book::with('tag')->where()

        } elseif ($request->column_filter == 'tags') {
            $queryData = null;
            $queryData = Book::with('publisher', 'author', 'tag')
//              ->where('Available_units','LIKE',$request->unit_start. '%' .$request->unit_end)
//              ->where('Unit_price','LIKE',$request->price_start. '%' .$request->price_end)
                ->whereHas('tag', function ($query) use ($request) {
                    $query->where('Tag_name', 'LIKE', '%' . $request->search_by_word . '%');
                });
            if (isset($request->unit_start)) {
//              dd($queryData);
                $queryData = $queryData->whereBetween('Available_units', [$request->unit_start, $request->unit_end]);
            }
            if (isset($request->price_start)) {
                $queryData = $queryData->whereBetween('Unit_price', [$request->price_start, $request->price_end]);
            }
            return response()->json(['data' => $queryData->paginate(4)], 200);
        } elseif ($searchFilter[$request->column_filter] === 'Book_title') {
            $queryData = null;
            $queryData = Book::with('publisher', 'author', 'tag')
                ->where('Book_title', 'LIKE', '%' . $request->search_by_word . '%');
            if (isset($request->unit_start)) {
//              dd($queryData);
                $queryData = $queryData->whereBetween('Available_units', [$request->unit_start, $request->unit_end]);
            }
            if (isset($request->price_start)) {
                $queryData = $queryData->whereBetween('Unit_price', [$request->price_start, $request->price_end]);
            }
            return response()->json(['data' => $queryData->paginate(4)], 200);
        } elseif ($searchFilter[$request->column_filter] === 'Book_publisher') {
            $queryData = null;
            $queryData = Book::with('publisher', 'author', 'tag')
                ->whereHas('publisher', function ($query) use ($request) {
                    $query->where('Publisher_name', 'LIKE', '%' . $request->search_by_word . '%');
                });
            if (isset($request->unit_start)) {
//              dd($queryData);
                $queryData = $queryData->whereBetween('Available_units', [$request->unit_start, $request->unit_end]);
            }
            if (isset($request->price_start)) {
                $queryData = $queryData->whereBetween('Unit_price', [$request->price_start, $request->price_end]);
            }
            return response()->json(['data' => $queryData->paginate(4)], 200);
        } else {
            $queryData = null;
            $queryData = Book::with('publisher', 'author', 'tag')
                ->whereHas('author', function ($query) use ($request) {
                    $query->where('First_name', 'LIKE', '%' . $request->search_by_word . '%')
                        ->orWhere('Middle_name', 'LIKE', '%' . $request->search_by_word . '%')
                        ->orWhere('Last_name', 'LIKE', '%' . $request->search_by_word . '%');
                });
            if (isset($request->unit_start)) {
//              dd($queryData);
                $queryData = $queryData->whereBetween('Available_units', [$request->unit_start, $request->unit_end]);
            }
            if (isset($request->price_start)) {
                $queryData = $queryData->whereBetween('Unit_price', [$request->price_start, $request->price_end]);
            }
            return response()->json(['data' => $queryData->paginate(4)], 200);
        }
    }

    public function searchBuyIdOrTitle (Request $request) {
        $book = null;
        if($request->BOOK_id && $request->BOOK_id !=''){
            $book = Book::with('publisher','author')->where('BOOK_id','LIKE' ,'%'.$request->BOOK_id.'%' )->get();
        }else{
            $book = Book::with('publisher','author')->where('Book_title','LIKE','%'.$request->Book_title.'%')->get();
        }


        return response()->json(['data' => $book],200);
    }

    public function  addOrder(Request $request){

        $newOrder = Order::create([
            'Book_id' => $request->Book_id,
            'numberOfUnits' => $request->numberOfUnits,
            'buyerName' => $request->buyerName,
            'buyerAdress' => $request->buyerAdress,
            'phone' => $request->phone ,
            'purchaseDate' => $request->purchaseDate,
            'nationalId' => $request->nationalId,
            'paymentMethod' => $request->paymentMethod,
            'totalPrice' => $request->totalPrice,
        ]);
//        dd((int)$request->Book_id);
        $book = Book::find($request->Book_id);
        $book->update([
            'Available_units' => $book->Available_units - $request->numberOfUnits,
        ]);
//        $test = Order::with('book')->get();
        return response()->json(['success' => 'true'],200);

    }

}
