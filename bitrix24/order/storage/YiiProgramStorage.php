<?php

namespace common\bitrix24\order\storage;

use backend\models\CountDaysProgram;
use backend\models\Programs;

class YiiProgramStorage implements StorageInterface
{
    private int $program_id;
    private int $count_day;

    /**
     * @param int $program_id
     * @param int $count_day
     */
    public function __construct(int $program_id, int $count_day)
    {
        $this->program_id = $program_id;
        $this->count_day = $count_day;
    }


    public function load(): array
    {
        $program = Programs::findOne($this->program_id);

        $countDaysProgram = CountDaysProgram::findOne(['program_id' => $this->program_id, 'count' => $this->count_day]);

        $minCountDayProgram = CountDaysProgram::find()
            ->select(['min(count_days_program.count) as min_count', 'price_day as min_price'])
            ->groupBy(['program_id', 'price_day'])
            ->where(['program_id' => $this->program_id])
            ->asArray()
            ->one();

        if(isset($program, $countDaysProgram)){
            return [
                'program_id' => $this->program_id,
                'count_day' => $this->count_day,
                'program_name' => $program->name,
                'calories' => $program->calories_info,
                'min_count_day' => $minCountDayProgram['min_count'] ?? null,
                'price_min' => $minCountDayProgram['min_price'] ?? null,
                'price' => $countDaysProgram->price,
                'price_fake' => $countDaysProgram->price_fake,
                'price_day' => $countDaysProgram->price_day,
                'price_first' => $countDaysProgram->price_first,
                'description' => $countDaysProgram->description
            ];
        }else{
            throw new \RuntimeException('Программа не найдена!');
        }
    }

    public function save($item)
    {
        // TODO: Implement save() method.
    }
}