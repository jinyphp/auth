<?php

namespace Jiny\Auth\Models;

/**
 * Backward compatibility alias for UserLanguage
 * @deprecated Use Jiny\Locale\Models\Language instead
 */
class_alias(\Jiny\Locale\Models\Language::class, UserLanguage::class);