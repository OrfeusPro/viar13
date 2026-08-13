<?php

namespace App\Http\Controllers;

use App;
use App\Models\FooterMenu;
use App\Models\HeaderMenu;
use Illuminate\Support\Arr;
use App\Models\Locale as Loc;
use Illuminate\Support\Facades\Request;
use App\Models\WhyAreYouLeavingQuestion;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

	protected $title;  //title
	protected $meta_desc;  //meta_desc
	protected $keywords;  //keywords
	protected $request;  //request
	protected $seo;  //request
	protected $creepingLine;

	protected $template; //Шаблон
	protected $vars = array(); //масив переменных передаваемые шаблону

	public function __construct(Request $request)
	{
        $this->template = config('theme.resource') . '.index';
		$this->request = $request;
	}

	protected function renderOutput()
	{
		$this->vars = Arr::add($this->vars, 'creepingLine', $this->creepingLine);
		$this->vars = Arr::add($this->vars, 'request', $this->request);
		$this->vars = Arr::add($this->vars, 'seo', $this->seo);
		$this->vars = Arr::add($this->vars, 'meta_desc', $this->meta_desc);
		$this->vars = Arr::add($this->vars, 'title', $this->title);
		$this->vars = Arr::add($this->vars, 'locales', Loc::all());
		// $this->vars = Arr::add($this->vars, 'why_are_you_leaving_questions', WhyAreYouLeavingQuestion::All()->where('is_show', 1)->translate(App::getLocale(), 'ru'));

		return view($this->template)->with($this->vars);
	}

    public function get_global_data()
    {
        return [
            'locales' => Loc::all(),
            'menu_items1' => HeaderMenu::where('menu_pos', 1)->orderBy('order', 'asc')->where('is_show', 1)->get()->translate(App::getLocale(), 'lv'),
            'menu_items2' => HeaderMenu::where('menu_pos', 2)->orderBy('order', 'asc')->where('is_show', 1)->get()->translate(App::getLocale(), 'lv'),
            'menu_items3' => HeaderMenu::where('menu_pos', 3)->orderBy('order', 'asc')->where('is_show', 1)->get()->translate(App::getLocale(), 'lv'),
            'menu_items1_bot' => FooterMenu::where('menu_pos', 1)->orderBy('order', 'asc')->get()->translate(App::getLocale(), 'lv'),
            'menu_items2_bot' => FooterMenu::where('menu_pos', 2)->orderBy('order', 'asc')->get()->translate(App::getLocale(), 'lv'),
            'menu_items3_bot' => FooterMenu::where('menu_pos', 3)->orderBy('order', 'asc')->get()->translate(App::getLocale(), 'lv'),
        ];
    }


}
