<?php

namespace App\Http\Controllers;

use App\Exports\BooksExport;
use App\Exports\OrdersExport;
use App\Models\Author;
use App\Models\Book;
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

//  print_r($request->file('Book_path'));
        $store_path = 'public/uploads';
        $file = $request->file('Book_path');
        $name = $file->getClientOriginalName();
//        $ext = $file->getClientOriginalExtension();
        $path = $file->storeAs($store_path, $name);

       $newBook =  Book::create([
            'Book_id' => $request->Book_id,
            'Book_title' => $request->Book_title,
            'Book_publisher' => $request->Book_publisher,
            'Publish_date' => $request->Publish_date,
            'Book_author' => $request->Book_author,
            'Book_path' => $path,
            'Available_units' => $request->Available_units,
             'Unit_price' => $request->Unit_price,
        ]);

       Tag::create([
          'Tag_name' => $request->tags,
           'Book_id'=> $newBook->Book_id,
       ]);

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
        if( $request->column_filter == 'Any'){
            $order = Order::with('book')
                           ->where('buyerName','LIKE','%'.$request->search_by_word.'%')
                           ->where('purchaseDate',$request->purchaseDate)
                          ->orWhereHas('book', function ($query) use($request) {
                              $query->where('Book_title','LIKE','%'.$request->search_by_word.'%')
                               ->orWhere('Book_publisher','LIKE','%'.$request->search_by_word.'%')
                               ->orWhere('Book_author','LIKE','%'.$request->search_by_word.'%');
                          })->paginate(4);
            return response()->json(['data' => $order],200);

    }
        elseif ($searchFilter[$request->column_filter] == 'Book_title' ||
                $searchFilter[$request->column_filter]== 'Book_publisher' ||
                $searchFilter[$request->column_filter] == 'Book_author'
               ){
            $orders = Order::with('book')->where('purchaseDate',$request->purchaseDate)
                ->whereHas('book',function ($query) use($searchFilter, $request){
                  $query->where($searchFilter[$request->column_filter],$request->search_by_word);
                })->paginate(4);
            return response()->json(['data' => $orders],200);
        }
        else {
          $orders = Order::with('book')->where( $searchFilter[$request->column_filter],$request->search_by_word)
                      ->where('purchaseDate',$request->purchaseDate)->paginate(4);
            return response()->json(['data' => $orders],200);
        }
    }

    public function search(Request $request) {
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
      if( $request->column_filter == 'Any'){
          $book_columns = Schema::getColumnListing('books');
          $exclude_columns = [
              'Unit_price',
              'Available_units',
              'created_at',
              'updated_at',
               'id',
          ];

          $select = array_diff($book_columns, $exclude_columns);
          $result = Book::with('tag')
              ->where('Book_id', 'LIKE', '%'.$request->search_by_word.'%')
              ->orWhere('Book_title',  'LIKE', '%'.$request->search_by_word.'%')
              ->orWhere('Book_publisher',  'LIKE', '%'.$request->search_by_word.'%')
              ->orWhere('Publish_date',  'LIKE', '%'.$request->search_by_word.'%')
              ->orWhere('Book_author',  'LIKE', '%'.$request->search_by_word.'%')
              ->orWhereHas('tag', function ($query) use($request) {
                  $query->where('Tag_name', 'LIKE', '%'.$request->search_by_word.'%');
              })->paginate(4);

            $this->dataToExport = $result;

          return response()->json(['data' => $result],200);

//         $book = Book::with('tag')->where()



      }
      elseif ($request->column_filter == 'tags'){
          $book = Book::with('tag')
              ->where('Available_units','LIKE',$request->unit_start. '%' .$request->unit_end)
              ->where('Unit_price','LIKE',$request->price_start. '%' .$request->price_end)
              ->whereHas('tag',function ($query) use ($request){
              $query->where('Tag_name', 'LIKE', '%'.$request->search_by_word.'%');
            })->paginate(4);
          $this->dataToExport = $book;

          return response()->json(['data' => $book],200);
      }
      else{
          $book = Book::with('tag')
              ->where($searchFilter[$request->column_filter],$request->search_by_word)
              ->where('Available_units','LIKE',$request->unit_start. '%' .$request->unit_end)
              ->where('Unit_price','LIKE',$request->price_start. '%' .$request->price_end)
              ->paginate(4);

          $this->dataToExport = $book;

            return response()->json(['data' => $book],200);
      }

    }

    public function searchBuyIdOrTitle (Request $request) {
        $book = null;
        if($request->BOOK_id && $request->BOOK_id !=''){
            $book = Book::where('BOOK_id','LIKE' ,'%'.$request->BOOK_id.'%' )->get();
        }else{
            $book = Book::where('Book_title','LIKE','%'.$request->Book_title.'%')->get();
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
