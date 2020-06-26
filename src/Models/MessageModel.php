<?php namespace Tatter\Outbox\Models;

use CodeIgniter\Model;
use Tatter\Pushover\Entities\Message;

class MessageModel extends Model
{
	protected $table      = 'pushover_messages';
	protected $primaryKey = 'id';
	protected $returnType = Message::class;

	protected $useSoftDeletes = false;
	protected $useTimestamps  = true;
	protected $skipValidation = true;

	protected $allowedFields = [
		'message', 'title', 'url', 'url_title', 'html', 'monospace',
		'sound', 'attachment', 'device', 'timestamp',
		'priority', 'retry', 'expire', 'callback', 'receipt',
	];
}
