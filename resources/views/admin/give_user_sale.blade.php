@extends('layots.common')
@section('title', '404')

    <style>
        .contacts {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media(min-width:1024px) {
            .contacts {
                display: block;
                height: auto;
                margin-top: 300px;
                margin-bottom: 300px;
            }
        }

    </style>

@section('content')
    <section class="contacts">
        <div class="container">
            <h1 style="text-align: center;font-size: calc(4rem - 15px);">404</h1>
        </div>
    </section>
@endsection
