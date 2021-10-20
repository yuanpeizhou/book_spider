<?php

namespace App\Http\Requests;

use App\Exceptions\ParamsErrorException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use stdClass;


/**
 * 重写FormRequest类，实现验证场景
 * Class AbstractRequest
 * @package App\Http\Requests
 */
class AbstractRequest extends FormRequest
{

    public $scenes = [];

    public $currentScene; //当前场景

    public $autoValidate = false; //是否注入之后自动验证

    public $extendRules;

    public function authorize():bool
    {
        return true;
    }

    /**兼容资源控制器,加载id */
    public function validationData(): array
    {
        $data = parent::validationData();

        $data['id'] = $this->route('id');

        return $data;
    }

    /**
     * 设置场景
     * @param $scene
     * @return $this
     */
    public function scene($scene): AbstractRequest
    {
        $this->currentScene = $scene;
        return $this;
    }

    /**
     * 使用扩展rule,回会覆盖scene方法中同字段的验证方法
     * @param string $name
     * @return AbstractRequest
     */
    public function with(string $name = ''): AbstractRequest
    {
        if (is_array($name)) {
            $this->extendRules = array_merge($this->extendRules[], array_map(function ($v) {

                return Str::camel($v);
            }, $name));
        } else if (is_string($name)) {
            $this->extendRules[] = Str::camel($name);
        }
        return $this;
    }

    /**
     * 覆盖自动验证方法
     */
    public function validate($scene = '')
    {
        if ($this->autoValidate) {
            $this->handleValidate();
        }
    }

    /**
     * 手动验证
     * @param string $scene
     * @return array
     */
    public function check(string $scene = ''): array
    {
        if ($scene) {
            $this->currentScene = $scene;
        }
        return $this->handleValidate();
    }

    /**
     * 根据场景获取规则
     * @return array|mixed
     */
    public function getRules()
    {
        $rules = $this->container->call([$this, 'rules']);
        $newRules = [];
        if ($this->extendRules) {
            $extendRules = array_reverse($this->extendRules);
            foreach ($extendRules as $extendRule) {
                if (method_exists($this, "{$extendRule}Rules")) { //合并场景规则
                    $rules = array_merge($rules, $this->container->call(
                        [$this, "{$extendRule}Rules"]
                    ));
                }
            }
        }

        if ($this->currentScene && isset($this->scenes[$this->currentScene])) {
            $sceneFields = is_array($this->scenes[$this->currentScene]) ? $this->scenes[$this->currentScene] : explode(',', $this->scenes[$this->currentScene]);
            foreach ($sceneFields as $field) {
                if (array_key_exists($field, $rules)) {
                    $newRules[$field] = $rules[$field];
                }
            }
            return $newRules;
        }

        return $rules;
    }

    /**
     * 覆盖设置 自定义验证器
     * @param $factory
     * @return mixed
     */
    public function validator($factory)
    {
        return $factory->make(
            $this->validationData(),
            $this->getRules(),
            $this->messages(),
            $this->attributes()
        );
    }

    /**
     * 最终验证方法
     */
    protected function handleValidate():array
    {
        if (!$this->passesAuthorization()) {
            $this->failedAuthorization();
        }

        $instance = $this->getValidatorInstance();

        if ($instance->fails()) {
            throw new ParamsErrorException($instance->errors()->all()[0]);
        }

        $results = [];
        $missingValue = new stdClass();
        foreach (array_keys($this->getRules()) as $key) {
            $value = data_get($this->validationData(), $key, $missingValue);

            if ($value !== $missingValue) {
                Arr::set($results, $key, $value);
            }
        }

        /**驼峰转蛇形 */
        foreach ($results as $key => $value) {
            $results[Str::snake($key)] = $value;
        }

        return $results;
    }
}
