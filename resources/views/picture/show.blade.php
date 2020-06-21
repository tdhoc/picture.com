@extends('layouts.app')

@section('head')
<link href="{{ asset('/css/picture-show.css') }}" rel="stylesheet" type="text/css">
<title> Picture </title>
@endsection

@section('content')
<div id="page_container">
                            
    <div class="custom-breadcrumb">
        <a class="breadcrumb-element breadcrumb-with-icon breadcrumb-blue" title="Pictures" href='{{asset("/")}}'>
            <span class="glyphicon glyphicon-home"></span> 
        </a>
        <a class="breadcrumb-element breadcrumb-with-icon breadcrumb-green" title="Category" href="">
            <span>{{ $category->name }}</span>
        </a>
        <a class="breadcrumb-element breadcrumb-with-icon" title="Subcategory" href="">
            <span>{{ $subcategory->name }}</span>
        </a>
        <a class="breadcrumb-element breadcrumb-with-icon" title="Picture Name" href="">
            <span>{{ $picture->tagstring }}</span>
        </a>
    </div>

    <div id="before_wallpaper_container">
        <div class="container">
            <div class="floatleft">
                <div class="action-container">
                    <span class='btn btn-info' title="Total View">
                        <i class="glyphicon glyphicon-eye-open"></i> 
                        <a>{{ $picture->view }} View</a>
                    </span>
                </div>

                <div class="action-container">
                    <span class='btn btn-warning' title="Total Downloaded">
                        <i class="glyphicon glyphicon-download"></i> {{ $picture->download }} Downloaded
                    </span>
                </div>
            </div>

            <div class="tags-container">
                <div id="list_tags">
                    <div class="tags-title">
                        <i class="glyphicon glyphicon-star-empty"></i>
                        <span>Author:</span>
                    </div>
                    <div class='author-element'>
                        <a>{{ $picture->author }}</a>
                    </div>
                    <div class="tags-title">
                        <i class="glyphicon glyphicon-tags"></i>
                        <span>Tags:</span>
                    </div>
                    @foreach ($picture->tag as $tag)
                        <div class='tag-element'>
                            <a>{{ $tag->name }}</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div></br></br>

    <div class="center img-container-desktop">
        <picture>
            <img width="{{ $picture->width }}" height="{{ $picture->height }}" src="{{ $picture->link }}" alt="{{ $picture->description }}"
                title="{{ $category->name.' '.$subcategory->name.' '.$picture->tagstring }}">
        </picture>
    </div></br>

    <div class="wallpaper-options">
        <div class="main-container center">
            <span class='btn btn-success btn-custom download-button' data-id="" data-type="">
                <i class="glyphicon glyphicon-download-alt"></i> Download Original Size: {{ $picture->resolution }}
            </span>
        </div>
    </div>
</div>

<div class="container">
    <div class="flex-wrapper">
        <div class="flex-item">
            <ul class="nav nav-tabs" role="tablist">
                <li class="active center">
                    <a class="light-blue" data-toggle="tab"><i class='glyphicon glyphicon-info-sign'></i> Submission Info</a>
                </li>
                <li>
                    @if ($previd > 0)
                    <div class="btn-group">
                        <a id="prev_page" rel="nofollow" class="btn btn-default prev-btn" href="{{ url('picture/show',$previd) }}">
                            <i class='glyphicon glyphicon-menu-left'></i> Previous
                        </a>
                    </div>
                    @endif
                    <div class="btn-group pull-right">
                        <a id="next_page" rel="nofollow" class="btn btn-default next-btn" href="{{ url('picture/show',$nextid) }}">
                            Next <i class='glyphicon glyphicon-menu-right'></i>
                        </a>
                    </div>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active">
                    <div class="panel panel-primary wallpaper-info-container">
                        <div class="submitter-wrapper">
                            <div class="submitter-info" style="background-image:url({{ $users->background }}); background-size:cover;">
                                <b>Created By:</b>
                                <div class="submitter-avatar">
                                    <img class="avatar-image" style="max-height:100px; margin: 10px;" alt="{{ $users->username }}" data-src="{{ $users->avatar }}" src="{{ $users->avatar }}">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"> {{ $users->username }}
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="{{ url('users/profile',$users->username) }}">
                                                    <i class="glyphicon glyphicon-user"></i> Profile
                                                </a>
                                            </li>
                                            <li>
                                                <a>
                                                    <i class="glyphicon glyphicon-picture"></i> {{ $users->count }} Pictures
                                                </a>
                                            </li>
                                            <li>
                                                <a>
                                                    <i class="glyphicon glyphicon-eye-open"></i> {{ $users->view }} View
                                                </a>
                                            </li>
                                            <li>
                                                <a>
                                                    <i class="glyphicon glyphicon-download"></i> {{ $users->download }} Downloaded
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <table class="table table-striped table-condensed" id="wallpaper_info_table">
                            <tbody>
                                <tr>
                                    <td>
                                        <span><i class='glyphicon glyphicon-stats'></i>
                                            &nbsp;&nbsp;<span class='inline-block'>ID /</span>
                                            <span class='inline-block'>Resolution /</span>
                                            <span class='inline-block'>Size / Type:</span>
                                        </span>
                                    </td>
                                    <td>
                                        <span>
                                            <span class='inline-block'>{{ $picture->id }} /</span>
                                            <span class='inline-block'>{{ $picture->resolution }} /</span>
                                            <span class='inline-block'>{{ $picture->size }} / {{ $picture->type }}</span>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span><i class='glyphicon glyphicon-time'></i>&nbsp;&nbsp;Date Added:</span>
                                    </td>
                                    <td>
                                        <span> {{ $picture->date }} ago </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><span><i class='glyphicon glyphicon-text-color'></i>&nbsp;&nbsp;Main Colors:</span>
                                    </td>
                                    <td class="colors-container">
                                        <a class='color-infos color-infos1' style='background-color:{{ $picture->color0 }}'></a>
                                        <a class='color-infos color-infos2' style='background-color:{{ $picture->color1 }}'></a>
                                        <a class='color-infos color-infos3' style='background-color:{{ $picture->color2 }}'></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="/js/bootstrap-3.4.1.min.js"></script>
@endsection