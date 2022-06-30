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
        $validated = $this->request->validate([
            'search_by_word' => 'required',
            'column_filter' => 'required',
        ]);
        if ($this->request->column_filter == 'Any') {

            $result = Book::with('publisher', 'author', 'tag')
                ->where('Book_id', 'LIKE', '%' . $this->request->search_by_word . '%')
                ->orWhere('Book_title', 'LIKE', '%' . $this->request->search_by_word . '%')
                ->orWhere('Publish_date', 'LIKE', '%' . $this->request->search_by_word . '%')
                ->orWhereHas('publisher', function ($query) {
                    $query->where('Publisher_name', 'LIKE', '%' . $this->request->search_by_word . '%');
                })->orWhereHas('author', function ($query){
                    $query->where('First_name', 'LIKE', '%' . $this->request->search_by_word . '%')
                        ->orWhere('Middle_name', 'LIKE', '%' . $this->request->search_by_word . '%')
                        ->orWhere('Last_name', 'LIKE', '%' . $this->request->search_by_word . '%');
                })->orWhereHas('tag', function ($query)   {
                    $query->where('Tag_name', 'LIKE', '%' . $this->request->search_by_word . '%');
                })->get();
              return  $result;

//         $book = Book::with('tag')->where()

        } elseif ($this->request->column_filter == 'tags') {
            $queryData = null;
            $queryData = Book::with('publisher', 'author', 'tag')
//              ->where('Available_units','LIKE',$request->unit_start. '%' .$request->unit_end)
//              ->where('Unit_price','LIKE',$request->price_start. '%' .$request->price_end)
                ->whereHas('tag', function ($query)  {
                    $query->where('Tag_name', 'LIKE', '%' . $this->request->search_by_word . '%');
                });
            if (isset($this->request->unit_start)) {
//              dd($queryData);
                $queryData = $queryData->whereBetween('Available_units', [$this->request->unit_start, $this->request->unit_end]);
            }
            if (isset($this->request->price_start)) {
                $queryData = $queryData->whereBetween('Unit_price', [$this->request->price_start, $this->request->price_end]);
            }
            return  $queryData->get();
        }
        elseif ($searchFilter[$this->request->column_filter] === 'Book_title') {
            $queryData = null;
            $queryData = Book::with('publisher', 'author', 'tag')
                ->where('Book_title', 'LIKE', '%' . $this->request->search_by_word . '%');
            if (isset($this->request->unit_start)) {
//              dd($queryData);
                $queryData = $queryData->whereBetween('Available_units', [$this->request->unit_start, $this->request->unit_end]);
            }
            if (isset($this->request->price_start)) {
                $queryData = $queryData->whereBetween('Unit_price', [$this->request->price_start, $this->request->price_end]);
            }
            return  $queryData->get();

        } elseif ($searchFilter[$this->request->column_filter] === 'Book_publisher') {
            $queryData = null;
            $queryData = Book::with('publisher', 'author', 'tag')
                ->whereHas('publisher', function ($query)   {
                    $query->where('Publisher_name', 'LIKE', '%' . $this->request->search_by_word . '%');
                });
            if (isset($this->request->unit_start)) {
//              dd($queryData);
                $queryData = $queryData->whereBetween('Available_units', [$this->request->unit_start, $this->request->unit_end]);
            }
            if (isset($this->request->price_start)) {
                $queryData = $queryData->whereBetween('Unit_price', [$this->request->price_start, $this->request->price_end]);
            }
            return   $queryData->get();
        } else {
            $queryData = null;
            $queryData = Book::with('publisher', 'author', 'tag')
                ->whereHas('author', function ($query)  {
                    $query->where('First_name', 'LIKE', '%' . $this->request->search_by_word . '%')
                        ->orWhere('Middle_name', 'LIKE', '%' . $this->request->search_by_word . '%')
                        ->orWhere('Last_name', 'LIKE', '%' . $this->request->search_by_word . '%');
                });
            if (isset($this->request->unit_start)) {
//              dd($queryData);
                $queryData = $queryData->whereBetween('Available_units', [$this->request->unit_start, $this->request->unit_end]);
            }
            if (isset($this->request->price_start)) {
                $queryData = $queryData->whereBetween('Unit_price', [$this->request->price_start, $this->request->price_end]);
            }
            return   $queryData->get();
        }

    }
    public function map($book): array
    {
        $authorName = $book->author->First_name.' '.$book->author->Last_name;
        $tagsAsString = '';
        foreach ($book->tag as $tag){
            $tagsAsString.= ''. $tag->Tag_name.',';
        }
       $bookTags = substr($tagsAsString,0,-1);
        return [
            $book->Book_id,
            $book->publisher->Publisher_name,
            $book->Publish_date,
            $authorName,
            $bookTags,
            $book->Available_units,
            $book->Unit_price,
        ];
    }

    public function headings(): array
    {
        return ["ID","Publisher","Publish Date","Author","Tags","Availalbe Units","Unit Price"];
    }
}
