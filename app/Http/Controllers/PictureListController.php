<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Validator;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\MessageBag;

use App\Http\Model\Users;
use App\Http\Model\PictureTemp;
use App\Http\Model\Category;
use App\Http\Model\Subcategory;
use App\Http\Model\Tag;
use App\Http\Model\PictureTag;
use App\Http\Model\MainColor;
use App\Http\Model\Picture;
use Cookie;
use DB;

class PictureListController extends Controller
{

    /**
    * Sort
    *
    * @return json with cookie
    */
    public function sort(Request $request){
       /* if($request->view == "paged"){

        }elseif ($request->view == "simple") {

        }elseif ($request->view == "slideshow") {

        }elseif ($request->view == "infinite") {

        }*/
        if($request->min_resolution == "0x0"){
            $resolution_equals = ">=";
        }else{
            $resolution_equals = $request->$resolution_equals;
        }
        return response()->json(['error' => 'false'])->withCookie(cookie()->forever('view', $request->view))->withCookie(cookie()->forever('min_resolution', $request->min_resolution))->withCookie(cookie()->forever('resolution_equals', $resolution_equals))->withCookie(cookie()->forever('sort', $request->sort))->withCookie(cookie()->forever('elementsperpage', $request->elementsperpage));

    }

    public function getSubmissionInfos(Request $request){
        $id = $request->picture_id;
        $picture = Picture::where('id', $id)->first()->toArray();
        $uploader = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
        $count = Picture::where('users_id', $uploader['id'])->count();
        $subcategory = Subcategory::where('id', $picture['subcategory_id'])->first()->toArray();
        $colors = MainColor::where('picture_id', $picture['id'])->get()->toArray();
        $tags = PictureTag::where('picture_id', $picture['id'])->get()->toArray();
        $returndata = '<div class="submitter-info"><div><b>Shared By:</b><div class="submitter-avatar"><img alt="'. $uploader['username'] . ' - Avatar" src="'.$uploader['avatar'].'"></div></div><div class="user-dropdown"><a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="#"><i class="el el-user"></i> ' . $uploader['username'] . ' <span class="caret"></span></a><ul class="dropdown-menu"><li><a href="/users/profile/'. $uploader['id'] .'"><i class="el el-eye-open"></i> Profile</a></li><li><a href="/users/profile/'. $uploader['id'] .'">' . $count . ' Wallpapers</a></li></ul></div></div><table class="table table-striped table-condensed table-user-info"><tbody><tr><td><span class="nav">&nbsp;&nbsp;ID</span></td><td><span>' . $picture["id"] . '</span></td></tr><tr><td><span class="nav">&nbsp;&nbsp;Tag</span></td><td><span>';

        foreach($tags as $key => $tag){
            $tagtemp = Tag::where('id', $tag['tag_id'])->first()->toArray();
            $returndata .= '<a title="' . $tagtemp["name"] . ' HD Wallpapers, Background Images" href="/by_tag/' . $tagtemp["id"] . '">' . $tagtemp["name"] . '</a>&nbsp;&nbsp;';

        }
        $returndata .= '</span></td></tr><tr><td><span class="nav">&nbsp;&nbsp;Views</span></td><td><span>' . $picture["view"] . '</span></td></tr><tr><td><span class="nav">&nbsp;&nbsp;Downloads</span></td><td><span>' . $picture["download"] . '</span></td></tr><tr><td><span class="nav">&nbsp;&nbsp;Resolution</span></td><td><span><a class="resolution-link" href="/by_resolution/' . $picture["resolution"] . '">' . $picture["resolution"] . ' <i class="el el-link"></i></a></span></td></tr><tr><td><span class="nav">&nbsp;&nbsp;Size / Type</span></td><td><span>' . $picture["size"] . ' / '. strtoupper(pathinfo($picture["link"])['extension']) . '</span></td></tr><tr><td><span class="nav">&nbsp;&nbsp;Date Added</span></td><td><span>' . date("d/m/Y", strtotime($picture['created_at'])) . '</span></td></tr><tr><td><span class="nav">&nbsp;&nbsp;Colors</span></td><td class="colors-container"><span>';
        $count = 1;
        foreach($colors as $key => $color){
            if($count <= 4){
                $returndata .= '<a class="color-infos color-infos' . $count . '" style="background-color:' . $color['color'] . '" href="/by_color/' . $color['color'] . '"></a>';
                $count++;
            }
        }
        $returndata .='</span></td></tr><tr><td colspan="2"><div class="slideTools"><span title="Download Wallpaper"! class="btn btn-primary btn-custom download-button align-top" data-id="' . $picture["id"] . '"><i class="el el-download-alt"></i> Download</span></div></td></tr></tbody></table>';
        return $returndata;
    }

    private function getCookie(Request $request){
        $view = $request->cookie('view');
        if($view == null){
            $view = 'paged';
        }
        $min_resolution = $request->cookie('min_resolution');
        if($min_resolution == null){
            $min_resolution = '0x0';
        }
        $resolution_min = explode("x", $min_resolution);
        $resolution_equals = $request->cookie('resolution_equals');
        if($resolution_equals == null){
            $resolution_equals = ">=";
        }
        $sort = $request->cookie('sort');
        if($sort == null || $sort == "newest"){
            $sort = "created_at";
        }
        $elementsperpage = $request->cookie('elementsperpage');
        if($elementsperpage == null){
            $elementsperpage = 30;
        }
        $cookie = [
            'view' => $view,
            'min_resolution' => $min_resolution,
            'resolution_equals' => $resolution_equals,
            'sort' => $sort,
            'elementsperpage' => $elementsperpage
        ];
        return $cookie;
    }
    private function getByCategoryPictureList($cookie, $id){
        $resolution_min = explode("x", $cookie['min_resolution']);
        $picturelist = [];
        $picturewsubcategorylist = Picture::with(['subcategory', 'tag'])->orderBy($cookie['sort'], 'desc')->get()->toArray();
        $category = ['id' => $id];
        $category['name'] = Category::where('id', $id)->first()->toArray()['name'];
        foreach ($picturewsubcategorylist as $picture) {
            $resolution = explode('x', $picture['resolution']);
            if($cookie['resolution_equals'] == '>='){
                if($resolution[0] >= $resolution_min[0] && $resolution[1] >= $resolution_min[1] && $picture['subcategory']['category_id'] == $id){
                    $uploader = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
                    $picture['uploader'] = $uploader;
                    unset($picture['subcategory']['category_id']);
                    unset($picture['subcategory_id']);
                    unset($picture['users_id']);
                    $picture['resolutionwh'] = ['width' => $resolution[0],'height' => $resolution[1]];
                    $picture['tagstring'] = '';
                    foreach($picture['tag'] as $key => $tag){
                        unset($picture['tag'][$key]['picture_id']);
                        $picture['tag'][$key]['name'] = Tag::where('id', $tag['tag_id'])->get(['name'])->toArray()[0]['name'];
                        $picture['tagstring'] .= ' '.$picture['tag'][$key]['name'];
                    }
                    $picture['tagstring'] = trim($picture['tagstring'], ' ');
                    array_push($picturelist, $picture);
                }
            }elseif ($cookie['resolution_equals'] == '=') {
                if($resolution[0] == $resolution_min[0] && $resolution[1] == $resolution_min[1] && $picture['subcategory']['category_id'] == $id){
                    $uploader = Users::where('id', $picture['users_id'])->get(['id','username', 'avatar'])->toArray()[0];
                    $picture['uploader'] = $uploader;
                    unset($picture['subcategory']['category_id']);
                    unset($picture['subcategory_id']);
                    unset($picture['users_id']);
                    $picture['resolutionwh'] = ['width' => $resolution[0],'height' => $resolution[1]];
                    $picture['tagstring'] = '';
                    foreach($picture['tag'] as $key => $tag){
                        unset($picture['tag'][$key]['picture_id']);
                        $picture['tag'][$key]['name'] = Tag::where('id', $tag['tag_id'])->get(['name'])->toArray()[0]['name'];
                        $picture['tagstring'] .= ' '.$picture['tag'][$key]['name'];
                    }
                    $picture['tagstring'] = trim($picture['tagstring'], ' ');
                    array_push($picturelist, $picture);
                }
            }
        }
        return $picturelist;
    }

    public function getInfiniteData(Request $request){
        $cookie = $this->getCookie($request);
        $view = $cookie['view'];
        $min_resolution = $cookie['min_resolution'];
        $resolution_equals = $cookie['resolution_equals'];
        $sort = $cookie['sort'];
        $elementsperpage = $cookie['elementsperpage'];
        if($request->by == "by_category"){
            $picturelist = $this->getByCategoryPictureList($cookie, $request->id);
            $maxpage = CEIL(count($picturelist)/$elementsperpage);
            $page = $request->next;
            if ($maxpage == 0) {
                $maxpage = 1;
            }
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_category/' . $request->id);
            }elseif($page > $maxpage){
                return response()->json(['error' => "out of picture"]);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
            }
            return response()->json(['error' => 'false', 'picturelist' => $returndata, 'page' => $page]);
        }
    }
    /**
    * Get by_category page
    *
    * @return view
    */
    public function getByCategory(Request $request){
        $cookie = $this->getCookie($request);
        $view = $cookie['view'];
        $min_resolution = $cookie['min_resolution'];
        $resolution_equals = $cookie['resolution_equals'];
        $sort = $cookie['sort'];
        $elementsperpage = $cookie['elementsperpage'];

        $page = $request->page;
        $picturelist = $this->getByCategoryPictureList($cookie, $request->id);
        $category = ['id' => $request->id];
        $category['name'] = Category::where('id', $request->id)->first()->toArray()['name'];
        //get max page
        $maxpage = CEIL(count($picturelist)/$elementsperpage);
        if ($maxpage == 0) {
            $maxpage = 1;
        }
        $taglist = [];
        foreach($picturelist as $picturetemp){
            foreach ($picturetemp['tag'] as $tagtemp) {
                if(!array_key_exists($tagtemp['tag_id'], $taglist)){
                    $taglist[$tagtemp['tag_id']]['numofpicture'] = 0;
                    $taglist[$tagtemp['tag_id']]['id'] = $tagtemp['tag_id'];
                    $taglist[$tagtemp['tag_id']]['name'] = $tagtemp['name'];
                }
                $taglist[$tagtemp['tag_id']]['numofpicture'] += 1;
            }
        }
        usort($taglist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($taglist) > 10) {
            $taglist = array_slice($taglist, 0, 10);
        }

        $subcategorylist = Subcategory::where('category_id', $category['id'])->get()->toArray();
        $userlist = [];
        foreach ($subcategorylist as $key => $subcategory) {
            $usertemp = Picture::groupBy('users_id')->select('users_id', DB::raw('count(*) as numofpicture'))->where('subcategory_id', $subcategory['id'])->get()->toArray();
            foreach ($usertemp as $value) {
                if(!array_key_exists($value['users_id'], $userlist)){
                    $userlist[$value['users_id']]['numofpicture'] = 0;
                    $userlist[$value['users_id']]['id'] = $value['users_id'];
                    $userlist[$value['users_id']]['username'] = Users::where('id', $value['users_id'])->first()->toArray()['username'];
                }
                $userlist[$value['users_id']]['numofpicture'] += $value['numofpicture'];
            }
            $subcategorylist[$key]["numofpicture"] = Picture::where('subcategory_id', $subcategory['id'])->count();
            unset($subcategorylist[$key]['category_id']);
        }

        usort($userlist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($userlist) > 10) {
            $userlist = array_slice($userlist, 0, 10);
        }

        usort($subcategorylist, function ($item1, $item2) {
            return $item2['numofpicture'] <=> $item1['numofpicture'];
        });
        if (count($subcategorylist) > 10) {
            $subcategorylist = array_slice($subcategorylist, 0, 10);
        }

        $column1 = '<div class="panel-heading center">
                        Popular Subcategories In This Category
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($subcategorylist as $subcategorytemp) {
            $column1 .= '<li>
                                <a title="' . $subcategorytemp['name'] . ' HD Picture " href="/by_subcategory/' .$subcategorytemp['id']. '/1">
                                    <span class="badge pull-right">' .$subcategorytemp['numofpicture']. '</span>' . $subcategorytemp['name'] . '
                                </a>
                            </li>';
        }
        $column1 .= '</ul>
                </div>
                <div class="panel-footer center">
                    <strong>
                        <a href="/sub_categories/1">
                            <i class="el el-th-list"></i> View All Subcategories
                        </a>
                    </strong>
                </div>';


        $column2 = '<div class="panel-heading center">
                        Popular Tags In This Category
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($taglist as $tagtemp) {
            $column2 .= '<li>
                                <a title="' . $tagtemp['name'] . ' HD Picture " href="/by_tag/' .$tagtemp['id']. '/1">
                                    <span class="badge pull-right">' .$tagtemp['numofpicture']. '</span>' . $tagtemp['name'] . '
                                </a>
                            </li>';
        }
        $column2 .= '</ul>
                </div>
                <div class="panel-footer center">
                    <strong>
                        <a href="/tags">
                            <i class="el el-th-list"></i> View All Tags
                        </a>
                    </strong>
                </div>';

        $column3 = '<div class="panel-heading center">
                        Most Uploader In This Category
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">';
        foreach ($userlist as $usertemp) {
            $column3 .= '<li>
                                <a title="' . $usertemp['username'] . ' HD Picture " href="/users/profile/' .$usertemp['id']. '">
                                    <span class="badge pull-right">' .$usertemp['numofpicture']. '</span>' . $usertemp['username'] . '
                                </a>
                            </li>';
        }
        $column3 .= '</ul></div>';
        $footer = ['column1' => $column1, 'column2' => $column2, 'column3' => $column3];
        $sortcurrent = "";
        if ($sort == "created_at") {
            $sort = "newest";
            $sortcurrent .= "Newest";
        }elseif ($sort == "view") {
            $sortcurrent .= "Most Viewed";
        }elseif ($sort == "download") {
            $sortcurrent .= "Most Download";
        }
        if ($min_resolution != "0x0") {
            $sortcurrent .= ", Resolution ".$resolution_equals." ".$min_resolution;
        }
        $sortdata = [
            'current' => $sortcurrent,
            'view' => 'infinite',
            'resolution' => $min_resolution,
            'resolution_equals' => $resolution_equals,
            'sort' => $sort,
            'elementsperpage' => $elementsperpage
        ];
        $pagetitle = count($picturelist) . ' ' . $category['name'] . ' Pictures';


        if($view == "paged"){
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_category/' . $request->id . '/1');
            }elseif($page > $maxpage){
                return redirect('by_category/' . $request->id . '/'.$maxpage);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
                $pagedata = "";
                if($maxpage > 1 && $maxpage <= 13){
                    $pagedata .= '<ul class="pagination">';
                    if($page == 1){
                        $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'. ($page - 1) .'">&#60;&nbsp;Previous</a></li>';
                    }
                    for($i = 1; $i <= $maxpage; $i++) {
                        if($page == $i){
                            $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                        }else{
                            $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                        }
                    }
                    if($page == $maxpage){
                        $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                    }else{
                        $pagedata .= '<li><a id="next_page" href="/by_category/'.$request->id.'/'. ($page + 1) .' ">Next&nbsp;&#62;</a></li> ';
                    }
                    $pagedata .= "</ul>";
                }elseif ($maxpage > 13) {
                    $pagedata .= '<ul class="pagination">';
                    if($page < 6){
                        if($page == 1){
                            $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                        }else{
                            $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'. ($page-1) .'">&#60;&nbsp;Previous</a></li>';
                        }
                        for($i = 1; $i <= 10; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_category/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_category/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                    }elseif ($page > $maxpage-6) {
                        $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_category/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $maxpage-9; $i <= $maxpage; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        if($page == $maxpage){
                            $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                        }else{
                            $pagedata .= '<li><a id="next_page" href="/by_category/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                        }
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_category/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $page - 3; $i <= $page+4; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_category/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_category/'.$request->id.'/'.($page+1) .'">Next&nbsp;&#62;</a></li>';
                    }
                    $pagedata .= '</ul><div class="quick-jump"><div class="input-group"><input type="text" class="form-control" placeholder="Page # / '.$maxpage.'"><span class="form-control btn-default quick-jump-btn">Go!</span></div></div>';
                }

                $peta = '<a class="breadcrumb-element" title="'.$category['name'].' HD Wallpapers | Background Images" href="/by_category/'. $category["id"] .'/1">
                            <span>'. $category["name"]  .'</span>
                        </a>
                        <span class="breadcrumb-element breadcrumb-page">
                            <span>Page</span> <span>#'. $page .'</span>
                        </span>';

                return view('list.pagination', array('picturelist' => $returndata, 'pagetitle' => $pagetitle, 'by' => 'by_category', 'id' => $request->id, 'sort' => $sortdata, 'pagedata' => $pagedata, 'current' => $page, 'null' => $peta, 'category' => $category, 'footer' => $footer));
            }
        }elseif ($view == "infinite") {
            if($page == null || $page != 1 || !isset($page)){
                return redirect('by_category/' . $request->id . '/1');
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }

                $peta = '<a class="breadcrumb-element" title="'.$category['name'].' HD Wallpapers | Background Images" href="/by_category/'. $category["id"] .'/1">
                            <span>'. $category["name"]  .'</span>
                        </a>';

                return view('list.infinite', array('picturelist' => $returndata, 'maxpage' => $maxpage, 'by' => 'by_category', 'id' => $request->id, 'pagetitle' => $pagetitle, 'sort' => $sortdata, 'current' => $page, 'null' => $peta, 'category' => $category, 'footer' => $footer));
            }
        }elseif ($view == "slideshow") {
            if($page == null || $page < 1 || !isset($page)){
                return redirect('by_category/' . $request->id . '/1');
            }elseif($page > $maxpage){
                return redirect('by_category/' . $request->id . '/'.$maxpage);
            }else{
                $returndata = [];
                for($i = (($page - 1) * $elementsperpage), $count = 0; $i < count($picturelist) && $count < $elementsperpage; $count++, $i++){
                    array_push($returndata, $picturelist[$i]);
                }
                $pagedata = "";
                if($maxpage > 1 && $maxpage <= 13){
                    $pagedata .= '<ul class="pagination">';
                    if($page == 1){
                        $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'. ($page - 1) .'">&#60;&nbsp;Previous</a></li>';
                    }
                    for($i = 1; $i <= $maxpage; $i++) {
                        if($page == $i){
                            $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                        }else{
                            $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                        }
                    }
                    if($page == $maxpage){
                        $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                    }else{
                        $pagedata .= '<li><a id="next_page" href="/by_category/'.$request->id.'/'. ($page + 1) .' ">Next&nbsp;&#62;</a></li> ';
                    }
                    $pagedata .= "</ul>";
                }elseif ($maxpage > 13) {
                    $pagedata .= '<ul class="pagination">';
                    if($page < 6){
                        if($page == 1){
                            $pagedata .= '<li class="active"><a id="prev_page" href="#">&#60;&nbsp;Previous</a></li>';
                        }else{
                            $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'. ($page-1) .'">&#60;&nbsp;Previous</a></li>';
                        }
                        for($i = 1; $i <= 10; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_category/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_category/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                    }elseif ($page > $maxpage-6) {
                        $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_category/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $maxpage-9; $i <= $maxpage; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        if($page == $maxpage){
                            $pagedata .= '<li class="active"><a id="next_page" href="#">Next&nbsp;&#62;</a></li>';
                        }else{
                            $pagedata .= '<li><a id="next_page" href="/by_category/'.$request->id.'/'.($page+1 ).'">Next&nbsp;&#62;</a></li>';
                        }
                    }else{
                        $pagedata .= '<li><a id="prev_page" href="/by_category/'.$request->id.'/'.($page-1) .'">&#60;&nbsp;Previous</a></li><li><a href="/by_category/'.$request->id.'/1">1</a></li><li><a>...</a></li>';
                        for($i = $page - 3; $i <= $page+4; $i++) {
                            if($page == $i){
                                $pagedata .= '<li class="active"><a>'.$i.'</a></li>';
                            }else{
                                $pagedata .= '<li><a href="/by_category/'.$request->id.'/'.$i.'">'.$i.'</a></li>';
                            }
                        }
                        $pagedata .= '<li><a>...</a></li><li><a href="/by_category/'.$request->id.'/'.$maxpage.'">'.$maxpage.'</a></li><li><a id="next_page" href="/by_category/'.$request->id.'/'.($page+1) .'">Next&nbsp;&#62;</a></li>';
                    }
                    $pagedata .= '</ul><div class="quick-jump"><div class="input-group"><input type="text" class="form-control" placeholder="Page # / '.$maxpage.'"><span class="form-control btn-default quick-jump-btn">Go!</span></div></div>';
                }

                $peta = '<a class="breadcrumb-element" title="'.$category['name'].' HD Wallpapers | Background Images" href="/by_category/'. $category["id"] .'/1">
                                <span>'. $category["name"]  .'</span>
                            </a>';

                return view('list.slideshow', array('picturelist' => $returndata, 'pagetitle' => $pagetitle, 'by' => 'by_category', 'id' => $request->id, 'sort' => $sortdata, 'pagedata' => $pagedata, 'next' => $page+1, 'null' => $peta, 'category' => $category, 'footer' => $footer));
            }   
        }
        /*if($request->page == null || $request->page < 1 ){
            $url = 'by_category/' . $request->id . '/1';
            return redirect('by_category/' . $request->id . '/1');
        }else{

            return view('list', array('id' => $request->id, 'page' => $request->page));
        }*/
        /*$value = $request->cookie('name');
        if($value == ''){
            Cookie::queue('name', 'MyValue');
            return view('list', array('id' => $request->id, 'page' => $request->page, 'name' => $value));
        }else{
            return view('list', array('id' => $request->id, 'page' => $request->page, 'name' => $value));
        }*/
    }
}