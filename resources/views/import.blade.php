@extends('parent')

@section('title', 'メルカカード番号インポート')

@section('body')
    <h1>メルカードPOS番号マッチングシステム</h1>
    <form method="POST" enctype="multipart/form-data">
        @csrf
        <div>
            <label for="pos">POS(CSV形式)</label>
            <div>
                <input type="file" name="pos" id="pos">
            </div>
        </div>
        <div>
            <label for="pos">通販(CSV形式)</label>
            <div>
                <input type="file" name="list" id="list">
            </div>
        </div>
        <div>
            <button type="submit">送信</button>
        </div>
    </form>
@endsection
