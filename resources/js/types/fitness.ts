export type MuscleGroup = {
    id: number;
    name: string;
};

export type Exercise = {
    id: number;
    user_id: number | null;
    muscle_group_id: number;
    name: string;
    equipment: string | null;
    description: string | null;
    video_url: string | null;
    muscle_group?: MuscleGroup;
};

export type WorkoutPlanDayExercise = {
    id: number;
    workout_plan_day_id: number;
    exercise_id: number;
    position: number;
    default_rest_seconds: number;
    target_sets: number | null;
    target_reps: number | null;
    target_weight: number | null;
    exercise?: Exercise;
};

export type WorkoutPlanDay = {
    id: number;
    workout_plan_id: number;
    name: string;
    order: number;
    exercises?: WorkoutPlanDayExercise[];
};

export type WorkoutPlan = {
    id: number;
    user_id: number;
    name: string;
    description: string | null;
    archived_at: string | null;
    days?: WorkoutPlanDay[];
    days_count?: number;
};

export type GymSet = {
    id: number;
    gym_session_exercise_id: number;
    weight: string | number;
    reps: number;
    duration: number | null;
    rest_seconds: number;
    rpe: string | number | null;
    completed: boolean;
};

export type GymSessionExercise = {
    id: number;
    gym_session_id: number;
    exercise_id: number;
    order: number;
    default_rest_seconds: number;
    exercise?: Exercise;
    sets?: GymSet[];
};

export type GymSession = {
    id: number;
    user_id: number;
    workout_plan_id: number | null;
    started_at: string;
    ended_at: string | null;
    notes: string | null;
    workout_plan?: WorkoutPlan;
    exercises?: GymSessionExercise[];
};

export type StravaAccount = {
    id: number;
    user_id: number;
    strava_id: number;
    expires_at: string;
};

export type StravaActivity = {
    id: number;
    user_id: number;
    strava_activity_id: number;
    name: string;
    sport_type: string;
    distance: string | number;
    moving_time: number;
    elapsed_time: number;
    elevation_gain: string | number;
    started_at: string;
    started_at_label: string;
};

export type WeeklySummary = {
    gym_sessions: number;
    strava_runs: number;
    strava_rides: number;
    strava_walks: number;
    weekly_volume: number;
    training_time_minutes: number;
    active_streak_days: number;
};

export type FitnessProfile = {
    fitness_goal: string | null;
    experience_level: string | null;
    training_days_per_week: number | null;
    weight: number | null;
    height_cm: number | null;
    preferred_units: 'kg' | 'lb';
};

export type SelectOption = {
    value: string;
    label: string;
};

export type DashboardPersonalization = {
    first_name: string;
    has_fitness_profile: boolean;
    goal_label: string | null;
    goal_tagline: string | null;
    level_label: string | null;
    weekly_target: number | null;
    workouts_this_week: number;
    weekly_volume: number;
    units: 'kg' | 'lb';
};

export type CalendarEvent = {
    type: string;
    label: string;
    id: number;
    name: string;
    completed?: boolean;
    workout_plan_id?: number | null;
    workout_plan_day_id?: number | null;
    notes?: string | null;
};

export type CalendarPlanOption = {
    id: number;
    name: string;
    days?: { id: number; workout_plan_id: number; name: string; order: number }[];
};

export type CatalogFood = {
    id: number;
    name: string;
    category: string;
    category_label: string;
    calories_per_100g: number;
    protein_per_100g: number;
    carbs_per_100g: number;
    fat_per_100g: number;
    serving_size_g: number | null;
    serving_label: string | null;
};

export type FoodLogEntry = {
    id: number;
    user_id: number;
    consumed_on: string;
    meal_type: 'breakfast' | 'lunch' | 'dinner' | 'snack';
    name: string;
    quantity: string | null;
    calories: number;
    protein_g: number | null;
    carbs_g: number | null;
    fat_g: number | null;
};

export type NutritionMeal = {
    meal_type: string;
    label: string;
    sort_order: number;
    entries: FoodLogEntry[];
    calories: number;
};

export type NutritionTotals = {
    calories: number;
    protein_g: number;
    carbs_g: number;
    fat_g: number;
};

export type CalorieTarget = {
    target: number;
    bmr: number;
    tdee: number;
    goal_label: string | null;
};

export type NutritionWeekDay = {
    date: string;
    calories: number;
};
