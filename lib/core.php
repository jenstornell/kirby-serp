<?php
namespace KirbySerp;
use c;

class Archive {
    public $title_fallback = 'No title tag found! A title tag should be below 600px wide.';
    public $description_fallback = 'No meta description found! A meta description tag should be around 155 characters. Search engines choose if they want to use it or not.';

    function flag($title, $description) {
        if($title == '' || $title == $this->title_fallback) return 'danger';
        if($description == '' || $description == $this->description_fallback) return 'warning';
        return 'sucess';
    }

    function description($item) {
        if($item->seo_description()->isEmpty()) {
            return $this->description_fallback;
        }
        return $item->seo_description();
    }

    function title($item) {
        if($item->seo_title()->isEmpty()) {
            return $this->title_fallback;
        }
        return $item->seo_title();
    }

    function panel($item) {
        return u( c::get('plugin.serp.panel', 'panel') . '/pages/' . $item->id() . '/edit');
    }

    function callback($option, $defaults, $item) {
        $filter = c::get('plugin.serp.' . $option);
        
        if(is_callable($filter)) {
            $callback = call($filter, [
                'args' => [
                    'data' => $defaults,
                    'page' => $item
                ]
            ]);
        }
        if(isset($callback)) {
            return $callback;
        }
        return $defaults;
    }

    function results($item) {
        $title = $this->callback('filter.title', $this->title($item), $item);
        $description = $this->callback('filter.description', $this->description($item), $item);

        return $this->callback('filter.collection', [
            'title' => $title,
            'description' => $description,
            'url' => $this->callback('filter.url', $item->url(), $item),
            'uri' => $this->callback('filter.uri', $item->url(), $item),
            'panel' => $this->callback('filter.panel', $this->panel($item), $item),
            'flag' => $this->flag($title, $description)
        ], $item);
    }
}