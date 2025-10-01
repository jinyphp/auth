<?php

namespace Jiny\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserReview extends Model
{
    use HasFactory;

    protected $table = 'user_reviews';

    protected $fillable = [
        'user_id',
        'email',
        'name',
        'item',
        'item_id',
        'title',
        'review',
        'likes',
        'rank',
        'comments',
    ];

    protected $casts = [
        'likes' => 'integer',
        'rank' => 'integer',
        'comments' => 'integer',
    ];

    // 작성자
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 좋아요 증가
    public function incrementLikes()
    {
        $this->increment('likes');
    }

    // 좋아요 감소
    public function decrementLikes()
    {
        if ($this->likes > 0) {
            $this->decrement('likes');
        }
    }

    // 댓글 수 증가
    public function incrementComments()
    {
        $this->increment('comments');
    }

    // 댓글 수 감소
    public function decrementComments()
    {
        if ($this->comments > 0) {
            $this->decrement('comments');
        }
    }

    // 평점별 스코프
    public function scopeByRank($query, $rank)
    {
        return $query->where('rank', $rank);
    }

    // 아이템별 스코프
    public function scopeByItem($query, $item, $itemId = null)
    {
        $query->where('item', $item);
        if ($itemId) {
            $query->where('item_id', $itemId);
        }
        return $query;
    }

    // 인기순 정렬
    public function scopePopular($query)
    {
        return $query->orderBy('likes', 'desc');
    }

    // 최신순 정렬
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // 평점 높은순 정렬
    public function scopeHighRated($query)
    {
        return $query->orderBy('rank', 'desc');
    }
}