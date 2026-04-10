@extends('staff.layouts.master')

@section('title', isset($expense) ? 'Edit Expense' : 'Create Expense')

@section('content')

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h5>{{ isset($expense) ? 'Edit Expense' : 'Create Expense' }}</h5>
            </div>

            <div class="card-body">
                <form action="{{ isset($expense) ? route('staff.expenses.update', $expense->id) : route('staff.expenses.store') }}" method="POST">
                    @csrf
                    @if(isset($expense))
                        @method('PUT')
                    @endif

                    {{-- Category --}}
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $id => $name)
                                <option value="{{ $id }}"
                                    {{ isset($expense) && $expense->category_id == $id ? 'selected' : (old('category_id') == $id ? 'selected' : '') }}>
                                    {{ ucfirst($name) }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Amount --}}
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number"
                            name="amount"
                            id="amount"
                            step="0.01"
                            min="0.01"
                            class="form-control @error('amount') is-invalid @enderror"
                            value="{{ isset($expense) ? $expense->amount : old('amount') }}"
                            required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Expense Date --}}
                    <div class="mb-3">
                        <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                        <input type="date"
                            name="expense_date"
                            id="expense_date"
                            class="form-control @error('expense_date') is-invalid @enderror"
                            value="{{ isset($expense) ? $expense->expense_date : old('expense_date') }}"
                            required>
                        @error('expense_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Remarks --}}
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea name="remarks"
                            id="remarks"
                            rows="4"
                            class="form-control @error('remarks') is-invalid @enderror"
                            placeholder="Enter expense remarks (optional)">{{ isset($expense) ? $expense->remarks : old('remarks') }}</textarea>
                        @error('remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Buttons --}}
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary" onclick="this.disabled=true; this.innerText='Saving...'; this.form.submit();">
                            <i class="fas fa-save"></i>
                            {{ isset($expense) ? 'Update Expense' : 'Create Expense' }}
                        </button>

                        <a href="{{ route('staff.expenses.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
