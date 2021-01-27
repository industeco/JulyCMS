<?php

namespace July\Node;

use App\Entity\EntityMoldBase;
use App\Utils\Pocket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\EntityField\FieldTypes\FieldTypeManager;

class NodeType extends EntityMoldBase implements GetNodesInterface
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'node_types';

    /**
     * 获取实体类
     *
     * @return string
     */
    public static function getEntityModel()
    {
        return Node::class;
    }

    /**
     * 获取字段拼图（与字段相关的一组信息，用于组成表单）
     *
     * @param  string|null $langcode
     * @return array
     */
    public function retrieveFieldMaterials(string $langcode = null)
    {
        $langcode = $langcode ?: langcode('content');

        $pocket = new Pocket($this, 'field_materials');

        if ($materials = $pocket->get()) {
            $materials = $materials->value();
        }

        $modified = last_modified(backend_path('template/components/'));
        if (!$materials || $materials['created_at'] < $modified) {
            $materials = [];
            foreach ($this->fields as $field) {
                $materials[$field->id] = FieldTypeManager::findOrFail($field->translateTo($langcode))->getMaterials();
            }

            $materials = [
                'created_at' => time(),
                'materials' => $materials,
            ];

            $pocket->put($materials);
        }

        return $materials['materials'];
    }

    /**
     * 更新类型字段
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function updateRelatedFields(array $data)
    {
        DB::beginTransaction();

        $fields = [];

        foreach ($data as $index => $field) {
            $fields[$field['id']] = [
                'delta' => $index,
                'label' => $field['label'] ?? null,
                'description' => $field['description'] ?? null,
                'is_required' => boolval($field['is_required'] ?? false),
                'helpertext' => $field['helpertext'] ?? null,
            ];
        }
        // Log::info($fields);

        $this->fields()->sync($fields);

        DB::commit();
    }

    public function get_nodes()
    {
        return NodeSet::make($this->nodes->keyBy('id')->all());
    }
}
