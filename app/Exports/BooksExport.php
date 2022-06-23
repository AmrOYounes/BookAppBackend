<?php

namespace App\Exports;

use App\Models\Book;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class BooksExport implements FromCollection,WithHeadings,WithMapping
{
    private $request;
    public function __construct( $request)
    {
        $this->request = $request;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $searchFilter = [
            'Book title' => 'Book_title',
            'Book Publisher' => 'Book_publisher',
            'Book Author' => 'Book_author',
            'tags' => 'Tag_name',
        ];
        if( $this->request->column_filter == 'Any'){
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
                ->where('Book_id', 'LIKE', '%'.$this->request->search_by_word.'%')
                ->orWhere('Book_title',  'LIKE', '%'.$this->request->search_by_word.'%')
                ->orWhere('Book_publisher',  'LIKE', '%'.$this->request->search_by_word.'%')
                ->orWhere('Publish_date',  'LIKE', '%'.$this->request->search_by_word.'%')
                ->orWhere('Book_author',  'LIKE', '%'.$this->request->search_by_word.'%')
                ->orWhereHas('tag', function ($query)  {
                    $query->where('Tag_name', 'LIKE', '%'.$this->request->search_by_word.'%');
                })->get();
            return $result;



//            return response()->json(['data' => $result],200);

//         $book = Book::with('tag')->where()



        }
        elseif ($this->request->column_filter == 'tags'){
            $book = Book::with('tag')
                ->where('Available_units','LIKE',$this->request->unit_start. '%' .$this->request->unit_end)
                ->where('Unit_price','LIKE',$this->request->price_start. '%' .$this->request->price_end)
                ->whereHas('tag',function ($query){
                    $query->where('Tag_name', 'LIKE', '%'.$this->request->search_by_word.'%');
                })->get();
//            $this->dataToExport = $book;

            return  $book;
        }
        else{
            $book = Book::with('tag')
                ->where($searchFilter[$this->request->column_filter],$this->request->search_by_word)
                ->where('Available_units','LIKE',$this->request->unit_start. '%' .$this->request->unit_end)
                ->where('Unit_price','LIKE',$this->request->price_start. '%' .$this->request->price_end)
                ->paginate(4);

//            $this->dataToExport = $book;

            return $book;
        }

    }
    public function map($book): array
    {
        return [
            $book->Book_id,
            $book->Book_publisher,
            $book->Publish_date,
            $book->Book_author,
            $book->tag->Tag_name,
            $book->Available_units,
            $book->Unit_price,


        ];
    }

    public function headings(): array
    {
        return ["ID","Publisher","Publish Date","Author","Tags","Availalbe Units","Unit Price"];
    }
}
