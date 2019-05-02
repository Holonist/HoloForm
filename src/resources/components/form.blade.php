<form
    id="{{$form->id}}"
    method="{{$form->method}}"
    action="{{$form->action}}"
    enctype="{{$form->enctype}}"
    class="{{$form->classes}}"
    {{$form->extra}}
>
    {{csrf_field()}}
    @foreach ($form->inputs as $input)
        <div class='label-wrapper {{ (!in_array($input->type, ['hidden', 'checkbox'])) ? 'reverse' : '' }}'>
            @if($input->type == 'select')
                <select
                    @if($input->classes)
                        class="{{$input->classes}}"
                    @endif
                    placeholder="{{$input->id}}"
                    name="{{$input->name}}"
                    id="{{$input->id}}"
                    title='{{$input->comment ?? 0}}'
                    {{$input->extra}}
                >
                    @foreach($input->selectOptions as $option)
                        <option value='{{$option}}'>{{$option}}</option>
                    @endforeach
                </select>
            @elseif($input->type == 'textarea')
                <textarea
                    @if($input->classes)
                        class="{{$input->classes}}"
                    @endif
                    placeholder="{{$input->id}}"
                    name="{{$input->name}}"
                    id="{{$input->id}}"
                    title='{{$input->comment ?? 0}}'
                    {{$input->extra}}
                >{{nl2br($input->value)}}</textarea>
            @elseif($input->type == 'checkbox')
                <input
                    type='checkbox'
                    class='{{$input->classes}}'
                    name='{{$input->name}}'
                    id='{{$input->id}}'
                    title='{{$input->comment ?? 0}}'
                    {{$input->extra}}
                />
                <label for='{{$input->id}}'>{!!$input->value!!}</label>
            @else
                <input
                    @if($input->classes)
                        class="{{$input->classes}}"
                    @endif
                    @if($input->type !== 'submit')
                        name="{{$input->name}}"
                    @endif
                    id="{{$input->id}}"
                    placeholder="{{$input->id}}"
                    type="{{$input->type}}"
                    @if ($input->value)
                        value="{{$input->value}}"
                    @endif
                    title='{{$input->comment ?? 0}}'
                    {{$input->extra}}
                />
            @endif

            @if(!in_array($input->type, ['hidden', 'checkbox', 'submit', 'button']))
                <label class='small' for='{{$input->id}}'>
                    {{$input->id}}
                </label>
            @endif
        </div>
    @endforeach
</form>