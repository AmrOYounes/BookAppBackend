<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection,WithHeadings,WithMapping
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
            'Buyer name' => 'buyerName',
        ];
        $searchData = null;
        if( $this->request->column_filter == 'Any') {
            $searchData = Order::with('book', 'book.author', 'book.publisher')->
            where('numberOfUnits', 'LIKE', '%' . $this->request->search_by_word . '%')
                ->orWhere('buyerName', 'LIKE', '%' . $this->request->search_by_word . '%')
                ->orWhere('totalPrice', 'LIKE', '%' . $this->request->search_by_word . '%')
                ->orWhereHas('book', function ($query){
                    $query->where('Book_id', 'LIKE', '%' . $this->request->search_by_word . '%')
                        ->orWhere('Book_title', 'LIKE', '%' . $this->request->search_by_word . '%');
                })->orWhereHas('book.publisher', function ($query){
                    $query->where('Publisher_name', 'LIKE', '%' . $this->request->search_by_word . '%');

                })->orWhereHas('book.author', function ($query)  {
                    $query->where('First_name', 'LIKE', '%' . $this->request->search_by_word . '%')
                        ->orWhere('Middle_name', 'LIKE', '%' . $this->request->search_by_word . '%')
                        ->orWhere('Last_name', 'LIKE', '%' . $this->request->search_by_word . '%');
                });
            if (isset($this->request->purchaseDate)) {
                $searchData = $searchData->where('purchaseDate', $this->request->purchaseDate);
            }
            return  $searchData-> get();
        }


        elseif ($searchFilter[$this->request->column_filter] == 'buyerName'){
            $searchData = Order::with('book','book.author','book.publisher')->
            where('buyerName','LIKE','%'.$this->request->search_by_word.'%');

            if(isset($this->request->purchaseDate)){
                $searchData= $searchData->where('purchaseDate',$this->request->purchaseDate);
            }
            return   $searchData->get();
        }


        elseif ($searchFilter[$this->request->column_filter] == 'Book_title'){
            $searchData = Order::with('book','book.author','book.publisher')->
            whereHas('book', function ($query){
                $query->where('Book_title','LIKE','%'.$this->request->search_by_word.'%');
            });

            if(isset($this->request->purchaseDate)){
                $searchData= $searchData->where('purchaseDate',$this->request->purchaseDate);
            }
            return   $searchData->get();
        }


        elseif ($searchFilter[$this->request->column_filter] == 'Book_publisher'){
            $searchData = Order::with('book','book.author','book.publisher')->
            whereHas('book.publisher', function ($query) {
                $query->where('Publisher_name','LIKE','%'.$this->request->search_by_word.'%');
            });

            if(isset($this->request->purchaseDate)){
                $searchData= $searchData->where('purchaseDate',$this->request->purchaseDate);
            }
            return  $searchData->get();
        }

        else{
            $searchData = Order::with('book','book.author','book.publisher')->
            whereHas('book.author', function ($query) {
                $query->where('First_name','LIKE','%'.$this->request->search_by_word.'%')
                    ->orWhere('Middle_name','LIKE','%'.$this->request->search_by_word.'%')
                    ->orWhere('Last_name','LIKE','%'.$this->request->search_by_word.'%');
            });

            if(isset($this->request->purchaseDate)){
                $searchData= $searchData->where('purchaseDate',$this->request->purchaseDate);
            }
            return   $searchData->get();
        }

    }
    public function map($order): array
    {
        return [
            $order->Book_id,
            $order->book->Book_title,
            $order->book->publisher->Publisher_name,
            $order->purchaseDate,
            $order->book->author->First_name.' '.$order->book->author->Last_name,
            $order->numberOfUnits,
            $order->totalPrice,
            $order->buyerName,
        ];
    }

    public function headings(): array
    {
        return ["ID","Book title","Publisher","Purchase Date","Author","Purchased Units","Total Cost","Buyer name"];
    }


}
