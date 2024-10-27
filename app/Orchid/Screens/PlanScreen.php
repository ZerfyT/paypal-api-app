<?php

namespace App\Orchid\Screens;

use App\Models\Plan;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class PlanScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'plans' => Plan::all(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'PlanScreen';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Create Plan')
                ->modal('planModal')
                ->method('create')
                ->icon('plus')
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('plans', [
                TD::make('name', 'Name'),
                TD::make('description', 'Description'),
                TD::make('price', 'Price'),
                TD::make('currency', 'Currency'),
                TD::make('interval_unit', 'Interval Unit'),
                TD::make('interval_count', 'Interval Count'),
                TD::make('trial_period_days', 'Trial Period Days')->defaultHidden(),
                TD::make('status', 'Status')->filter(TD::FILTER_SELECT, ['active' => 'Active', 'inactive' => 'Inactive']),
                TD::make('braintree_plan_id', 'Braintree Plan ID'),
                TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (Plan $plan) => DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([

                        // Link::make(__('Edit'))
                        //     ->route('platform.plans.edit', $plan->id)
                        //     ->icon('bs.pencil'),
                        Button::make(__('Edit'))
                            ->icon('bs.pencil')
                            ->modal('planModal')
                            ->method('edit')
                            ->parameters([
                                'plan' => $plan,
                            ]),

                        Button::make(__('Delete'))
                            ->icon('bs.trash3')
                            ->confirm(__('Are you sure you want to delete this plan?'))
                            ->method('remove', [
                                'id' => $plan->id,
                            ]),
                    ])),
                // TD::make('Actions')
                //     ->align(TD::ALIGN_CENTER)
                //     ->render(function (Plan $plan) {
                //         return ModalToggle::make('Edit')
                //             ->method('edit')
                //             ->modal('planModal')
                //             ->parameters([
                //                 'plan' => $plan,
                //             ]);
                //         }),
                // TD::make('Actions')
                //     ->align(TD::ALIGN_CENTER)
                //     ->render(function (Plan $plan) {
                //         return ModalToggle::make('Delete')
                //             ->method('destroy')
                //             ->modal('planModal')
                //             ->parameters([
                //                 'plan' => $plan,
                //             ]);
                //     })
            ])
                ->title('Plans'),

            Layout::modal('planModal', Layout::rows([
                Input::make('plan.name')->title('Name')->placeholder('Enter plan name')->required(),
                Input::make('plan.description')->title('Description')->placeholder('Enter plan description'),
                Input::make('plan.price')->title('Price')->placeholder('Enter plan price')->required(),
                Input::make('plan.currency')->title('Plan Currency')->placeholder('Enter plan currency')->required(),
                Input::make('plan.interval_unit')->title('Interval Unit')->placeholder('Enter interval unit')->required(),
                Input::make('plan.interval_count')->title('Interval Count')->placeholder('Enter interval count')->required(),
                Input::make('plan.trial_period_days')->title('Trial Period Days')->placeholder('Enter trial period days'),
                Input::make('plan.status')->title('Status')->placeholder('Enter plan status'),
                Input::make('plan.braintree_plan_id')->title('Braintree Plan ID')->placeholder('Enter braintree plan id'),
            ]))
                ->title('Create Plan'),
        ];
    }
}
