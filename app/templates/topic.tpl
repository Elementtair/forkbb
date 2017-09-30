@section('crumbs')
      <ul class="f-crumbs">
@foreach($crumbs as $cur)
@if($cur[2])
        <li class="f-crumb"><a href="{!! $cur[0] !!}" class="active">{{ $cur[1] }}</a></li>
@else
        <li class="f-crumb"><a href="{!! $cur[0] !!}">{{ $cur[1] }}</a></li>
@endif
@endforeach
      </ul>
@endsection
@section('linkpost')
@if($newPost !== null)
        <div class="f-link-post">
@if($newPost === false)
          __('Topic closed')
@else
          <a class="f-btn" href="{!! $newPost !!}">{!! __('Post reply') !!}</a>
@endif
        </div>
@endif
@endsection
@section('pages')
        <nav class="f-pages">
@foreach($pages as $cur)
@if($cur[2])
          <span class="f-page active">{{ $cur[1] }}</span>
@elseif($cur[1] === 'space')
          <span class="f-page f-pspacer">{!! __('Spacer') !!}</span>
@elseif($cur[1] === 'prev')
          <a rel="prev" class="f-page f-pprev" href="{!! $cur[0] !!}">{!! __('Previous') !!}</a>
@elseif($cur[1] === 'next')
          <a rel="next" class="f-page f-pnext" href="{!! $cur[0] !!}">{!! __('Next') !!}</a>
@else
          <a class="f-page" href="{!! $cur[0] !!}">{{ $cur[1] }}</a>
@endif
@endforeach
        </nav>
@endsection
@extends('layouts/main')
    <div class="f-nav-links">
@yield('crumbs')
@if($newPost || $pages)
      <div class="f-links-b clearfix">
@yield('pages')
@yield('linkpost')
      </div>
@endif
    </div>
    <section class="f-main f-topic">
      <h2>{{ $topicName }}</h2>
@foreach($posts as $post)
      <article id="p{!! $post['id'] !!}" class="f-post{!! $post['poster_gender'].$post['poster_online'] !!} clearfix">
        <div class="f-post-header clearfix">
          <h3>{{ $topicName }} - #{!! $post['post_number'] !!}</h3>
          <span class="left"><time datetime="{{ $post['posted_utc'] }}">{{ $post['posted'] }}</time></span>
          <span class="right"><a href="{!! $post['link'] !!}" rel="bookmark">#{!! $post['post_number'] !!}</a></span>
        </div>
        <div class="f-post-body clearfix">
          <address class="f-post-left clearfix">
            <ul class="f-user-info">
@if($post['poster_link'])
              <li class="f-username"><a href="{!! $post['poster_link'] !!}">{{ $post['poster'] }}</a></li>
@else
              <li class="f-username">{{ $post['poster'] }}</li>
@endif
@if($post['poster_avatar'])
              <li class="f-avatar">
                <img alt="{{ $post['poster'] }}" src="{!! $post['poster_avatar'] !!}">
              </li>
@endif
              <li class="f-usertitle"><span>{{$post['poster_title']}}</span></li>
@if($post['poster_posts'])
              <li class="f-postcount"><span>{!! __('%s post', $post['poster_num_posts'], $post['poster_posts']) !!}</span></li>
@endif
            </ul>
@if($post['poster_info_add'])
            <ul class="f-user-info-add">
              <li><span>{!! __('Registered:') !!} {{ $post['poster_registered'] }}</span></li>
@if($post['poster_location'])
              <li><span>{!! __('From') !!} {{ $post['poster_location'] }}</span></li>
@endif
              <li><span></span></li>
            </ul>
@endif
          </address>

        </div>
        <div class="f-post-footer clearfix">
          <div class="f-post-left">
            <span></span>
          </div>
@if($post['controls'])
          <div class="f-post-right clearfix">
            <ul>
@foreach($post['controls'] as $key => $control)
              <li class="f-post{!! $key !!}"><a class="f-btn" href="{!! $control[0] !!}">{!! __($control[1]) !!}</a></li>
@endforeach
            </ul>
          </div>
@endif
        </div>
      </article>
@endforeach
    </section>
    <div class="f-nav-links">
@if($newPost || $pages)
      <div class="f-links-a clearfix">
@yield('linkpost')
@yield('pages')
      </div>
@endif
@yield('crumbs')
    </div>
